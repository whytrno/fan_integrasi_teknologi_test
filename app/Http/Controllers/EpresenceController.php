<?php

namespace App\Http\Controllers;

use App\Http\Requests\Epresence\EpresenceStoreRequest;
use App\Http\Requests\Epresence\EpresenceUpdateRequest;
use App\Http\Response\ApiResponse;
use App\Models\Epresence;
use Illuminate\Support\Facades\Auth;

class EpresenceController extends Controller
{
    public function myData()
    {
        return ApiResponse::handleResponse(function () {
            $user = Auth::user();

            $data = Epresence::where('user_id', $user->id)
                ->orderBy('waktu', 'asc')
                ->get();

            return ApiResponse::successResponse('Berhasil mendapatkan data', $this->formatEpresenceData($data));
        });
    }

    public function memberDataRaw()
    {
        return ApiResponse::handleResponse(function () {
            $memberId = Auth::user()->member->pluck('id');

            $data = Epresence::whereIn('user_id', $memberId)
                ->get();

            return ApiResponse::successResponse('Berhasil mendapatkan data', $data);
        });
    }

    public function memberDataFinal()
    {
        return ApiResponse::handleResponse(function () {
            $memberId = Auth::user()->member->pluck('id');

            $data = Epresence::whereIn('user_id', $memberId)
                ->orderBy('waktu', 'asc')
                ->get();

            return ApiResponse::successResponse('Berhasil mendapatkan data', $this->formatEpresenceData($data, true));
        });
    }

    // Note: Disini saya membuat store dengan logic yang saya buat dimana user/client tidak perlu mengirim body apapun, karena
    // type akan di sesuaikan sesuai database, jika di DB tidak ada data user IN, maka type otomatis IN dan jika sudah ada data IN maka type jadi OUT
    // waktu otomatis diisi dengan now()
    public function storeCustomLogic()
    {
        return ApiResponse::handleResponse(function () {
            $epresenceToday = $this->getTodayPresenceTypes();

            if (count($epresenceToday) >= 2) {
                return ApiResponse::errorResponse('Anda sudah absen IN dan OUT hari ini');
            }

            $data = null;
            $type = in_array('IN', $epresenceToday) ? 'OUT' : 'IN';

            $data = Epresence::create([
                'user_id' => Auth::id(),
                'type' => $type,
                'waktu' => now()
            ]);

            return ApiResponse::successResponse('Berhasil absensi ' . $type, $data);
        });
    }

    public function store(EpresenceStoreRequest $request)
    {
        return ApiResponse::handleResponse(function () use ($request) {
            $epresenceToday = $this->getTodayPresenceTypes();

            if (count($epresenceToday) >= 2) {
                return ApiResponse::errorResponse('Anda sudah absen IN dan OUT hari ini');
            }

            $type = $request->type;

            if (in_array($type, $epresenceToday)) {
                return ApiResponse::errorResponse('Anda sudah absen ' . $type . ' hari ini.');
            }

            $data = Epresence::create([
                'user_id' => Auth::id(),
                'type' => $type,
                'waktu' => $request->waktu
            ]);

            return ApiResponse::successResponse('Berhasil absensi ' . $type, $data);
        });
    }

    public function update(EpresenceUpdateRequest $request, int $id)
    {
        return ApiResponse::handleResponse(function () use ($request, $id) {
            $data = Epresence::findOrFail($id);
            $userNpp = Auth::user()->npp;
            $memberNpp = $data->user->npp_supervisor;

            if ($memberNpp !== $userNpp) {
                return ApiResponse::errorResponse('Anda tidak bisa mengubah data ini karena anda bukan supervisor dari pengguna ini');
            }

            $isApprove = $request->is_approve;
            $data->update([
                'is_approve' => $isApprove
            ]);

            $isApproveString = $isApprove ? 'APPROVE' : 'REJECT';

            return ApiResponse::successResponse('Berhasil perbarui status menjadi ' . $isApproveString, $data);
        });
    }

    // ==================================
    private function formatEpresenceData($data, bool $groupByUser = false)
    {
        $groupedData = $data->groupBy(fn($item) => $groupByUser ? $item->user_id : $item->waktu_date);

        $data = [];

        return $groupedData->map(function ($records, $key) use ($groupByUser) {
            $entry = [
                'id_user' => $groupByUser ? $key : Auth::id(),
                'nama_user' => $groupByUser ? $records->first()->user->nama : Auth::user()->nama,
                'tanggal' => $groupByUser ? null : $key,
                'waktu_masuk' => null,
                'waktu_pulang' => null,
                'status_masuk' => null,
                'status_pulang' => null,
            ];

            foreach ($records as $record) {
                if ($groupByUser) $entry['tanggal'] = $records->first()->waktu_date;

                if ($record->type === 'IN') {
                    $entry['waktu_masuk'] = $record->waktu_time;
                    $entry['status_masuk'] = $record->status;
                } elseif ($record->type === 'OUT') {
                    $entry['waktu_pulang'] = $record->waktu_time;
                    $entry['status_pulang'] = $record->status;
                }
            }

            return $entry;
        })->values();
    }

    private function getTodayPresenceTypes()
    {
        return Epresence::where('user_id', Auth::id())
            ->whereDate('created_at', today())
            ->pluck('type')
            ->toArray();
    }
}
