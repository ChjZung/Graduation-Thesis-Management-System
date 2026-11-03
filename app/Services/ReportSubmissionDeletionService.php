<?php
namespace App\Services;

use App\Models\{BaoCaoTienDo,DotNopBaoCao,KhoiBaoCaoTienDo,Nhom,TomTatBaoCao};
use App\Jobs\AnalyzeReportVersion;
use Illuminate\Support\Facades\{Cache,DB};
use Illuminate\Validation\ValidationException;

final class ReportSubmissionDeletionService
{
    public function deletePendingVersion(DotNopBaoCao $round,Nhom $group,string $reportId): array
    {
        $lock=Cache::store('file')->lock('ai-report:'.$reportId,420);
        if(!$lock->get())throw ValidationException::withMessages(['version'=>'AI đang xử lý phiên bản này. Hãy dừng phân tích, chờ tác vụ kết thúc rồi xóa.']);
        try {
            return DB::transaction(function() use($round,$group,$reportId){
                $round=DotNopBaoCao::lockForUpdate()->findOrFail($round->MaDotNop);
                abort_unless($group->deTai && (string)$group->deTai->MaDeTai===(string)$round->MaDeTai,403);
                $versions=KhoiBaoCaoTienDo::where('MaDotNop',$round->MaDotNop)->orderByDesc('LanNop')->orderByDesc('NgayNop')->lockForUpdate()->get();
                $round->setRelation('khoiBaoCaos',$versions);
                $owned=app(ReportMilestoneProgressService::class)->versionsForRound($round,$group);
                $target=$owned->firstWhere('MaKhoi',$reportId);
                abort_unless($target,403);
                if($target->TrangThai!=='Chờ duyệt' || filled($target->NguoiNhanXet) || filled($target->NgayNhanXet) || filled($target->NhanXetGV))
                    throw ValidationException::withMessages(['version'=>'Không thể xóa phiên bản đã được giảng viên nhận xét hoặc đánh giá.']);
                $parentId=$target->MaBaoCao;
                $snapshot=TomTatBaoCao::where('MaKhoi',$reportId)->lockForUpdate()->first();
                // Only remove this report's queued analysis. Other groups' jobs stay untouched.
                foreach(DB::table('jobs')->where('queue',config('ai_processing.queue','ai-reports'))->get(['id','payload']) as $job){
                    $payload=json_decode($job->payload,true);$command=$payload['data']['command']??'';
                    if(($payload['data']['commandName']??'')===AnalyzeReportVersion::class &&
                        str_contains($command,'s:8:"reportId";s:'.strlen($reportId).':"'.$reportId.'";'))DB::table('jobs')->where('id',$job->id)->delete();
                }
                // Unlink the legacy summary FK before deleting the submission.
                if($snapshot)$snapshot->delete();
                $target->delete();
                // Display numbering can change; the AI source identity must stay immutable.
                foreach($owned->reject(fn($row)=>$row->MaKhoi===$reportId)->sortBy('LanNop')->values() as $index=>$version){
                    $number=$index+1;$oldNumber=(int)$version->LanNop;
                    if($number===$oldNumber)continue;
                    $payload=app(AiReportProcessingService::class)->storedPayload($version);
                    $bound=$payload['_submission_identity']??$payload['source_identity']??[];
                    $bound['submission_version']=$bound['submission_version']??$oldNumber;
                    $payload['_submission_identity']=$bound;
                    $changes=['LanNop'=>$number,'TomTatJson'=>$payload];
                    if($version->TieuDe===$round->TenDotNop.' (Phiên bản '.$oldNumber.')')$changes['TieuDe']=$round->TenDotNop.' (Phiên bản '.$number.')';
                    $version->update($changes);
                }
                $remaining=KhoiBaoCaoTienDo::where('MaBaoCao',$parentId)->orderByDesc('NgayNop')->orderByDesc('LanNop')->first();
                $parent=BaoCaoTienDo::lockForUpdate()->find($parentId);
                if($parent && !$remaining){
                    TomTatBaoCao::where('MaBaoCao',$parentId)->delete();$parent->delete();
                }elseif($parent && $remaining){
                    $parent->update(['TieuDe'=>$remaining->TieuDe,'NoiDungBaoCao'=>$remaining->NoiDungBaoCao,
                        'NgayNop'=>$remaining->NgayNop,'TrangThai'=>$remaining->TrangThai,'TenFile'=>$remaining->TenFile,
                        'DuongDanFile'=>$remaining->DuongDanFile,'PhanTramHoanThanh'=>$remaining->PhanTramHoanThanh,
                        'NhanXet'=>$remaining->NhanXetGV,'NguoiXacNhan'=>$remaining->NguoiNhanXet,'NgayXacNhan'=>$remaining->NgayNhanXet]);
                    if($snapshot){
                        $data=app(AiReportProcessingService::class)->storedPayload($remaining);
                        if($data){
                            $snapshot->exists=false;
                            $snapshot->fill(['MaKhoi'=>$remaining->MaKhoi,'TenFile'=>$remaining->TenFile,'DuongDanFile'=>$remaining->DuongDanFile,
                                'TomTatJson'=>$data,'NoiDungAI'=>$data['overall_summary']??$remaining->TomTatAI??'',
                                'SoTrang'=>$data['analysis']['total_pages']??0,'TyLeHoanThanhAI'=>null,'CanCuDanhGia'=>null,
                                'CongViecDaHoanThanh'=>'','KhoKhan'=>'','KeHoachTuanToi'=>'','LoiXuLy'=>null,
                                'TrangThaiDanhGia'=>'chua_danh_gia','TrangThaiXuLy'=>($data['analysis']['status']??'')==='completed'?'hoan_thanh':'chua_hoan_thanh'])->save();
                        }
                    }
                }
                // Keep uploaded files: earlier versions may share the same physical path.
                $latest=$owned->first(fn($r)=>$r->MaKhoi!==$reportId);
                return ['round_id'=>$round->MaDotNop,'latest_version'=>$latest?->LanNop,'next_version'=>($latest?->LanNop??0)+1];
            },3);
        }finally{$lock->release();}
    }
}
