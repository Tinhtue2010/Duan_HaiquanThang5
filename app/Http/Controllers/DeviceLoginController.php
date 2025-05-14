<?php

namespace App\Http\Controllers;

use App\Models\DeviceLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DeviceLoginController extends Controller
{
    public function danhSachDangNhap()
    {
        $data = DeviceLogins::join('tai_khoan', 'device_logins.ten_dang_nhap', '=', 'tai_khoan.ten_dang_nhap')
            ->select('device_logins.*', 'tai_khoan.loai_tai_khoan')
            ->orderBy('device_logins.id', 'desc')
            ->where('device_logins.ten_dang_nhap', '!=', 'admin2')
            ->get();
        $currentTimeout = config('session.lifetime');
        return view('quan-ly-khac.danh-sach-dang-nhap', data: compact('data', 'currentTimeout'));
    }

    public function updateTimeout(Request $request)
    {
        $request->validate([
            'thoi_gian' => 'required|integer|min:1',
        ]);

        // Update the session lifetime in the .env file
        $path = base_path('.env');

        if (File::exists($path)) {
            file_put_contents($path, preg_replace(
                '/SESSION_LIFETIME=.*/',
                'SESSION_LIFETIME=' . $request->thoi_gian,
                file_get_contents($path)
            ));
        }

        // Optional: reload config
        config(['session.lifetime' => $request->thoi_gian]);

        return redirect()->back()->with('alert-success', 'Cập nhật thành công');
    }


    protected $maxSessions = 1;

    protected function authenticated(Request $request, $user)
    {
        $this->enforceSessionLimit($user->id);
    }

    protected function enforceSessionLimit($userId)
    {
        $currentSessionId = Session::getId();

        // Get active sessions for this user
        $userSessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderBy('last_activity', 'desc')
            ->get();

        // If sessions exceed limit, remove the oldest ones
        if ($userSessions->count() > $this->maxSessions) {
            $sessionsToRemove = $userSessions->slice($this->maxSessions);

            foreach ($sessionsToRemove as $session) {
                DB::table('sessions')->where('id', $session->id)->delete();
            }
        }
    }
}
