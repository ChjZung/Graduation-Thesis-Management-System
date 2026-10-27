"""Compact per-ID JSON schemas; Python performs referential and coverage checks."""
KINDS=['actors','precondition','main_flow','alternative_flow','postcondition','workflow','data_model','class_diagram','sequence_diagram','diagram','existence','content','implementation']
VERDICTS=['SUPPORTS','PARTIAL','DOES_NOT_SUPPORT','CONTRADICTS','NEEDS_VISUAL_REVIEW']
def string():return {'type':'string'}
def array(item):return {'type':'array','items':item}
def obj(properties):return {'type':'object','properties':properties,'required':list(properties)}
def enum(values):return {'type':'string','enum':values}
def plan_schema(items):
    return obj({'items':obj({i['item_id']:obj({'checks':array(obj({'description':string(),'requirement_excerpt':string(),'kind':enum(KINDS)}))}) for i in items})})
def match_schema(checks):
    return obj({'checks':obj({c['check_id']:obj({'status':enum(['CANDIDATE','NOT_FOUND','NEEDS_VISUAL_REVIEW']),'finding':string(),
        'evidence':array(obj({'source_id':enum(c['candidate_ids']) if c['candidate_ids'] else string(),'quote':string(),'occurrence':{'type':'integer'}}))}) for c in checks})})
def verify_schema(checks):
    return obj({'checks':obj({c['check_id']:obj({'verdict':enum(VERDICTS),'explanation':string(),
        'supported_elements':array(string()),'missing_elements':array(string()),'overclaim_detected':{'type':'boolean'},
        'safe_rewrite':string(),'supported_evidence_ids':array(enum([e['evidence_id'] for e in c['evidence']]))}) for c in checks})})
