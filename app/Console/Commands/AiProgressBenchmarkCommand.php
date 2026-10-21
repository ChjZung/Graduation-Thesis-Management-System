<?php
namespace App\Console\Commands;

use App\Services\AiEvaluationService;
use App\Services\AiSummaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AiProgressBenchmarkCommand extends Command
{
    protected $signature = 'ai:benchmark-reports {--dir=} {--limit=5} {--export=} {--method=heuristic_nlp} {--ground-truth=}';
    protected $description = 'Đo xử lý PDF/DOCX và đánh giá ba nhóm khi có bộ đáp án chuẩn';

    public function handle(AiSummaryService $aiService, AiEvaluationService $evaluation): int
    {
        $method = $this->option('method');
        if (!in_array($method,['heuristic_nlp','map_reduce'],true)) { $this->error('Phương pháp không hợp lệ.'); return 1; }
        $dir = $this->option('dir') ?: storage_path('app/public/baocao');
        if (!File::isDirectory($dir)) { $this->error('Thư mục dữ liệu không tồn tại.'); return 1; }
        $truth = [];
        try {
            if ($this->option('ground-truth')) $truth = json_decode(File::get($this->option('ground-truth')),true,512,JSON_THROW_ON_ERROR);
            if (!is_array($truth)) throw new \InvalidArgumentException('Đáp án chuẩn phải là JSON object theo tên tệp.');
        } catch (\Throwable $e) { $this->error('Không đọc được bộ đáp án chuẩn: '.$e->getMessage()); return 1; }
        $files = array_values(array_filter(File::allFiles($dir),fn($file)=>in_array(strtolower($file->getExtension()),['pdf','docx'],true)));
        usort($files,fn($a,$b)=>strcmp($a->getRelativePathname(),$b->getRelativePathname()));
        $runs = [];
        foreach (array_slice($files,0,max(1,(int)$this->option('limit'))) as $file) {
            $started = microtime(true);
            $result = $aiService->summarizePdf($file->getRealPath(),1,['method'=>$method]);
            $data = $result['data'] ?? [];
            $valid = !empty($result['success']);
            foreach (AiEvaluationService::GROUPS as $group) $valid = $valid && isset($data[$group]) && is_array($data[$group]);
            $annotations = $truth[$file->getRelativePathname()] ?? $truth[$file->getFilename()] ?? null;
            try { $scores = $evaluation->evaluate($data,$annotations); }
            catch (\InvalidArgumentException $e) { $this->error($file->getFilename().': '.$e->getMessage()); return 1; }
            $total = $matched = 0;
            foreach (AiEvaluationService::GROUPS as $group) foreach ($data[$group] ?? [] as $item) {
                $total++; if (!empty($item['verified'])) $matched++;
            }
            $runs[] = ['file'=>$file->getRelativePathname(),'requested_method'=>$method,'actual_source'=>$result['source'] ?? 'unknown',
                'processing_status'=>$data['analysis']['status'] ?? 'failed','json_valid'=>$valid,
                'latency_sec'=>round(microtime(true)-$started,3),'items'=>$total,'exact_source_matches'=>$matched,
                'source_match_rate'=>$total ? $matched/$total : null,'quality'=>$scores];
        }
        $display = fn($value)=>$value === null ? 'Chưa đánh giá' : number_format($value,3);
        $this->table(['Tệp','Nguồn thực tế','Giây','JSON','Precision','Recall','F1'],array_map(fn($r)=>[
            $r['file'],$r['actual_source'],$r['latency_sec'],$r['json_valid']?'Hợp lệ':'Lỗi',
            $display($r['quality']['precision']),$display($r['quality']['recall']),$display($r['quality']['f1']),
        ],$runs));
        $export = $this->option('export') ?: base_path('docs/ai_research/ai_benchmark_current.md');
        $md = "# Đánh giá tóm tắt báo cáo ba nhóm\n\nNgày chạy: ".now()->toIso8601String()."\n\n";
        $md .= "Precision/Recall/F1 chỉ được tính khi có đáp án chuẩn. Phương pháp: khớp nguyên văn bằng chứng theo từng nhóm; không phải độ chính xác ngữ nghĩa tổng quát. Không có dự đoán và đáp án thì điểm để trống.\n\n";
        $md .= "| Tệp | Nguồn thực tế | Giây | JSON | Precision | Recall | F1 |\n|---|---|---:|---|---:|---:|---:|\n";
        foreach ($runs as $r) $md .= '| '.str_replace('|','\\|',$r['file']).' | '.$r['actual_source'].' | '.$r['latency_sec'].' | '.($r['json_valid']?'Hợp lệ':'Lỗi').' | '.$display($r['quality']['precision']).' | '.$display($r['quality']['recall']).' | '.$display($r['quality']['f1'])." |\n";
        File::ensureDirectoryExists(dirname($export));
        File::put($export,$md);
        File::put($export.'.json',json_encode(['generated_at'=>now()->toIso8601String(),'runs'=>$runs],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
        $this->info('Đã xuất kết quả: '.$export);
        return 0;
    }
}
