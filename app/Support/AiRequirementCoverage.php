<?php
namespace App\Support;

/** Equal-weight evidence discovery only; never a completion grade or confidence score. */
final class AiRequirementCoverage
{
    public static function calculate(array $data): array
    {
        $items=array_values(array_filter($data['teacher_requirements']??[], 'is_array'));
        $binding=$data['requirements_binding_status']??'current';
        $available=(bool)$items && !in_array($binding,['stale','legacy_unverified'],true);
        $rows=[];$total=0.;$found=0;$leaves=0;$captions=0;
        foreach($items as $item) {
            $tree=self::tree((string)($item['text']??''));
            $walk=function(array $node,float $weight,int $depth) use (&$walk,&$rows,&$total,&$found,&$leaves,&$captions,$item): void {
                if($node['children']) {
                    $rows[]=['label'=>$node['label'],'depth'=>$depth,'weight'=>$weight,'found'=>null,'caption_only'=>false];
                    foreach($node['children'] as $child)$walk($child,$weight/count($node['children']),$depth+1);
                    return;
                }
                $refs=[];
                foreach($item['report_points']??[] as $point) {
                    // AI-generated checks do not define weights. An explicit child needs an exact label link.
                    if($depth>0 && self::key($point['title']??'')!==self::key($node['label']))continue;
                    if(!in_array($point['relation']??'', ['related_text'],true))continue;
                    if(in_array($point['check_status']??'', ['NOT_FOUND','INSUFFICIENT_EVIDENCE','NOT_PROCESSED','PENDING_VERIFICATION','PROCESSING_ERROR'],true))continue;
                    foreach($point['source_references']??[] as $ref)if(self::validRef($ref))$refs[]=$ref;
                }
                $has=(bool)$refs;$captionOnly=$has && !array_filter($refs,fn($r)=>!self::caption($r));
                $leaves++;if($has){$total+=$weight;$found++;if($captionOnly)$captions++;}
                $rows[]=['label'=>$node['label'],'depth'=>$depth,'weight'=>$weight,'found'=>$has,'caption_only'=>$captionOnly];
            };
            $walk($tree,100/max(1,count($items)),0);
        }
        $complete=($data['requirements_review']['status']??'')==='completed' &&
            in_array($data['_processing']['status']??'', ['', 'completed'],true);
        return ['available'=>$available,'percent'=>$available?round($total,2):null,
            'provisional'=>!$complete,'roots'=>count($items),'leaves'=>$leaves,'found'=>$found,
            'caption_only'=>$captions,'rows'=>$rows];
    }

    private static function validRef($ref): bool
    {
        return is_array($ref) && ($ref['quote_verified']??false)===true &&
            is_numeric($ref['page']??null) && (int)$ref['page']>0 && trim((string)($ref['quote']??''))!=='' &&
            !in_array($ref['semantic_support']??'', ['DOES_NOT_SUPPORT','NOT_SUPPORTED','CONTRADICTS','REJECTED'],true);
    }
    private static function caption(array $ref): bool
    {
        return in_array($ref['source_type']??'', ['CAPTION_REFERENCE','figure_caption','table_caption'],true) ||
            ($ref['semantic_support']??'')==='NEEDS_VISUAL_REVIEW';
    }
    private static function key(string $text): string
    {
        $text=preg_replace('/^\s*(?:\d+(?:\.\d+)*[.)]?|[a-z][.)]|[-•*])\s+/u','',$text);
        return mb_strtolower(preg_replace('/[\s\p{P}]+/u','',strip_tags($text)),'UTF-8');
    }

    /** Only explicit nested numbering/bullets in the original instructor item; no semantic splitting. */
    public static function tree(string $text): array
    {
        $lines=preg_split('/\R/u',trim($text));$first=array_shift($lines)??'';
        $root=['label'=>trim($first),'children'=>[]];$stack=[];
        foreach($lines as $line) {
            if(!trim($line))continue;
            if(!preg_match('/^(?<indent>[ \t]*)(?<marker>\d+(?:\.\d+)+[.)]?|\d+[.)]|[a-z][.)]|[-•*])\s+(?<text>.+)$/u',$line,$m))continue;
            $indent=strlen(str_replace("\t",'    ',$m['indent']));
            $decimal=preg_match('/^\d+(?:\.\d+)+/',$m['marker']);
            $level=$decimal?substr_count(rtrim($m['marker'],'.)'),'.'):null;
            $rank=$level!==null?$level:($indent+1);
            while($stack && $stack[count($stack)-1]['rank'] >= $rank)array_pop($stack);
            $node=['label'=>trim($m['marker'].' '.$m['text']),'children'=>[]];
            if(!$stack){$root['children'][]=$node;$path=[count($root['children'])-1];}
            else {
                $path=$stack[count($stack)-1]['path'];$parent=&$root;
                foreach($path as $index)$parent=&$parent['children'][$index];
                $parent['children'][]=$node;$path[]=count($parent['children'])-1;unset($parent);
            }
            $stack[]=['rank'=>$rank,'path'=>$path];
        }
        return $root;
    }
}
