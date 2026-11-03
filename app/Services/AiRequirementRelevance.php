<?php
namespace App\Services;

/** Subject/type gates; a retrieval heuristic, never an accuracy percentage. */
class AiRequirementRelevance
{
    public const VERSION = 'requirement-scope-5';
    public static function families(string $text): array
    {
        $t=AiRequirementCrossMatcher::removeDiacritics($text);
        $patterns=['database'=>'co so du lieu|\bcsdl\b|\berd\b|database|mo hinh quan he|quan he du lieu|nhom du lieu|bang du lieu|khoa chinh|khoa ngoai|primary key|foreign key',
            'class'=>'so do lop|bieu do lop|class diagram','sequence'=>'tuan tu|sequence','activity'=>'hoat dong|activity',
            'usecase'=>'use\s*case|usecase|ca su dung','ui'=>'giao dien|man hinh|frontend','testing'=>'kiem thu|thu nghiem|test case'];
        $got=[];
        foreach($patterns as $k=>$p){$p=str_replace(' ','\s*',$p);if(preg_match('~'.$p.'~u',$t))$got[]=$k;}
        return $got;
    }
    public static function relevant(string $req, string $text): bool
    {
        $r=AiRequirementCrossMatcher::removeDiacritics($req);$t=AiRequirementCrossMatcher::removeDiacritics($text);
        $r=preg_replace('/\s+/u',' ',trim($r));$t=preg_replace('/\s+/u',' ',trim($t));
        $wanted=self::families($r);$got=self::families($t);
        if($wanted && !array_intersect($wanted,$got))return false;
        $rc=str_replace(' ','',$r);$tc=str_replace(' ','',$t);
        if(str_contains($rc,'thietke') && !in_array('testing',$wanted) && in_array('testing',$got) && !str_contains($tc,'thietke'))return false;
        if(in_array('class',$wanted) && (str_contains($rc,'mucthietke') || str_contains($rc,'thietkesodolop')) &&
            str_contains($tc,'phantich') && !str_contains($tc,'mucthietke') && !str_contains($tc,'thietkesodolop'))return false;
        if(in_array('class',$wanted) && str_contains($rc,'mucphantich') && !str_contains($rc,'mucthietke') &&
            str_contains($tc,'mucthietke') && !str_contains($tc,'mucphantich'))return false;
        if(array_intersect(['sequence','usecase','activity'],$wanted)){
            $subjects=array_filter(['dang ky de tai','quan ly de tai','de xuat de tai','tien do','ho so bao ve','hoi dong','cham diem','sinh vien','dang nhap','nguoi dung'],fn($s)=>str_contains($rc,str_replace(' ','',$s)));
            if($subjects && !array_filter($subjects,fn($s)=>str_contains($tc,str_replace(' ','',$s))))return false;
        }
        if(in_array('ui',$wanted)){
            $modules=array_filter(['dang ky de tai','quan ly de tai','de xuat de tai','tien do','ho so bao ve','hoi dong','cham diem','dang nhap'],fn($s)=>str_contains($rc,str_replace(' ','',$s)));
            if($modules && !array_filter($modules,fn($s)=>str_contains($tc,str_replace(' ','',$s))))return false;
        }
        if($wanted)return true;
        $stop=explode(' ','thiet ke so do quan ly he thong bao cao trinh bay cac va cua noi dung xay dung');
        preg_match_all('/[a-z0-9]+/',$r,$a);preg_match_all('/[a-z0-9]+/',$t,$b);
        return count(array_intersect(array_diff(array_unique($a[0]),$stop),array_diff(array_unique($b[0]),$stop)))>=2;
    }
    public static function selectCandidate(string $req,array $cand): array
    {
        $refs=[];$summaries=[];$seen=[];
        foreach($cand['observations']??[] as $o){
            $content=$o['content']??'';if(!self::relevant($req,$content))continue;$accepted=false;
            foreach($o['source_references']??[] as $ref){
                if(!self::relevant($req,$ref['quote']??''))continue;
                $page=$ref['page']??null;$start=$cand['start_page']??null;$end=$cand['end_page']??null;
                if($page && $start && $end && !($start<=$page && $page<=$end))continue;
                $key=$page.':'.($ref['quote']??'');if(!isset($seen[$key])){$seen[$key]=true;$refs[]=$ref;}$accepted=true;
            }
            if($accepted && !in_array($content,$summaries,true))$summaries[]=$content;
        }
        return array_replace($cand,['source_references'=>$refs,'inherited_references'=>[],'summary'=>implode(' ',$summaries)]);
    }
}
