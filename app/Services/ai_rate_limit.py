"""Project/model-wide pacing shared by Python workers on this deployment."""
from contextlib import contextmanager
import hashlib
import json
import math
import sqlite3
import time
from pathlib import Path


class ProjectRateLimiter:
    def __init__(self, path, scope, rpm=4, lite_rpm=12, tpm=200000):
        self.path = Path(path)
        self.scope = hashlib.sha256(scope.encode()).hexdigest()
        self.rpm, self.lite_rpm, self.tpm = rpm, lite_rpm, tpm
        self.path.parent.mkdir(parents=True, exist_ok=True)
        with self.connect() as db:
            db.execute('CREATE TABLE IF NOT EXISTS calls (scope TEXT, model TEXT, at REAL, tokens INTEGER)')
            db.execute('CREATE INDEX IF NOT EXISTS calls_lookup ON calls(scope,model,at)')
            db.execute('CREATE TABLE IF NOT EXISTS cooldown (scope TEXT, model TEXT, until REAL, detail TEXT, PRIMARY KEY(scope,model))')

    @contextmanager
    def connect(self):
        db=sqlite3.connect(str(self.path),timeout=10)
        try:
            yield db
            db.commit()
        except BaseException:
            db.rollback()
            raise
        finally: db.close()

    def reserve(self, model, tokens, now=None):
        now = time.time() if now is None else now
        with self.connect() as db:
            db.execute('BEGIN IMMEDIATE')
            db.execute('DELETE FROM calls WHERE at <= ?', (now-60,))
            row = db.execute('SELECT until,detail FROM cooldown WHERE scope=? AND model=?', (self.scope,model)).fetchone()
            if row and row[0] > now:
                return row[0]-now, json.loads(row[1])
            calls = db.execute('SELECT at,tokens FROM calls WHERE scope=? AND model=? ORDER BY at', (self.scope,model)).fetchall()
            rpm = max(1, self.lite_rpm if 'flash-lite' in model else self.rpm)
            wait = max(0, calls[-1][0]+60/rpm-now) if calls else 0
            if len(calls) >= rpm:
                wait = max(wait,calls[-rpm][0]+60-now)
            # Conservative character-based estimate, not a replacement for Google's token counter.
            total = sum(row[1] for row in calls)
            for at, used in calls:
                if total+tokens <= self.tpm: break
                wait = max(wait,at+60-now)
                total -= used
            if tokens > self.tpm:
                return 0, {'code':'input_too_large','quota':'tokens','retry_after':0,'model':model}
            if wait > 0:
                return wait, {'code':'rate_limit','quota':'minute','retry_after':math.ceil(wait),'model':model}
            db.execute('INSERT INTO calls VALUES (?,?,?,?)', (self.scope,model,now,tokens))
            return 0, None

    def defer(self, model, detail, now=None):
        now = time.time() if now is None else now
        with self.connect() as db:
            db.execute('BEGIN IMMEDIATE')
            until = now+max(1, detail.get('retry_after',60))
            previous = db.execute('SELECT until FROM cooldown WHERE scope=? AND model=?',(self.scope,model)).fetchone()
            if not previous or until >= previous[0]:
                db.execute('INSERT OR REPLACE INTO cooldown VALUES (?,?,?,?)',(self.scope,model,until,json.dumps(detail)))
