<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Backup\BackupStore;
use App\Services\Backup\MysqlBackup;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MysqlBackupController extends Controller
{
    public function index(Request $request, MysqlBackup $backup, BackupStore $store)
    {
        $status = $request->validate(['status'=>'nullable|in:PENDING,RUNNING,SUCCESS,FAILED'])['status'] ?? null;
        $rows = array_values(array_filter($store->all(), fn ($row) => !isset($row['deleted_at'])
            && ($status === null || $row['status'] === $status)));
        $page = max(1, $request->integer('page', 1));
        $records = new LengthAwarePaginator(array_slice($rows, ($page-1)*20, 20), count($rows), 20, $page,
            ['path'=>$request->url(), 'query'=>$request->query()]);
        return view('admin.mysql_backup.index', ['records'=>$records, 'overview'=>$backup->overview(), 'status'=>$status]);
    }

    public function store(Request $request, MysqlBackup $backup)
    {
        try {
            $backup->enqueue((string) $request->user()->getAuthIdentifier());
        } catch (DomainException $error) {
            return back()->with('error', $error->getMessage());
        }
        return back()->with('success', 'Đã đưa yêu cầu vào hàng chờ sao lưu.');
    }

    public function download(Request $request, string $id, BackupStore $store)
    {
        $lock = null;
        $file = null;
        try {
            $lock = $store->lock();
            $record = $store->get($id);
            $file = fopen($store->verify($record), 'rb');
            if (!$file) throw new DomainException('Không mở được bản sao lưu.');
            $store->audit('downloaded', $id, (string) $request->user()->getAuthIdentifier());
            return response()->streamDownload(function () use ($file, $lock) {
                try { fpassthru($file); }
                finally { fclose($file); fclose($lock); }
            }, $id.'.sql', ['Content-Type'=>'application/octet-stream', 'Cache-Control'=>'private, no-store']);
        } catch (DomainException $error) {
            if (is_resource($file)) fclose($file);
            if (is_resource($lock)) fclose($lock);
            return back()->with('error', $error->getMessage());
        }
    }

    public function destroy(Request $request, string $id, BackupStore $store)
    {
        try {
            $store->exclusive(fn () => $store->delete($id, (string) $request->user()->getAuthIdentifier()));
        } catch (DomainException $error) {
            return back()->with('error', $error->getMessage());
        }
        return back()->with('success', 'Đã xóa bản sao lưu.');
    }
}

