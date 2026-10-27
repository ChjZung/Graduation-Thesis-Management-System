<?php
namespace App\Support;

/** Display counts only: quote matching is not semantic confidence or a grade. */
final class AiReportPresentation
{
    public static function metrics(array $data): array
    {
        $refs=[];$empty=0;$unlinked=0;
        $visit=function(array $items) use (&$refs): void {
            foreach($items as $item) foreach(($item['source_references']??[]) as $r) {
                if(empty($r['quote']) || !isset($r['page'])) continue;
                $key=hash('sha256',json_encode([$r['page'],$r['end_page']??$r['page'],preg_replace('/\s+/u',' ',trim($r['quote']))]));
                $flag=$r['quote_verified']??null;
                $refs[$key]=($refs[$key]??null)===true ? true : (is_bool($flag)?$flag:null);
            }
        };
        $visit($data['document_content']??[]);$visit($data['completed_work']??[]);
        foreach(($data['chapters']??[]) as $chapter) {
            $walk=function($node) use (&$walk,$visit): void {$visit($node['items']??[]);foreach(($node['sections']??[]) as $child)$walk($child);};
            $walk($chapter);
            if(empty($chapter['items']) && empty($chapter['summary']) && empty($chapter['sections']))$empty++;
        }
        foreach(($data['teacher_requirements']??[]) as $req) {
            if(!empty($req['report_points'])) {
                $visit($req['report_points']);
                foreach($req['report_points'] as $point) if(in_array($point['relation']??'pending',['not_found','pending']))$unlinked++;
            } else {
                $visit($req['report_matches']??[]);
                if(in_array($req['report_relation']??'pending',['not_found','pending']))$unlinked++;
            }
        }
        $a=$data['analysis']??[];
        return ['matched'=>count(array_filter($refs,fn($f)=>$f===true)),'references'=>count($refs),'measured'=>count(array_filter($refs,fn($f)=>is_bool($f))),
            'done'=>isset($a['analyzed_chunks'])?(int)$a['analyzed_chunks']:null,'total'=>isset($a['total_chunks'])?(int)$a['total_chunks']:null,
            'empty_chapters'=>$empty,'unlinked_requirements'=>$unlinked];
    }
}
