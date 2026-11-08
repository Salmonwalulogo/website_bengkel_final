<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan profil admin
     */
    public function profile()
    {
        $user = Auth::user();
        return view('backend.admin.settings.profile', compact('user'));
    }

    /**
     * Update profil admin
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'required|string|unique:users,username,' . $user->id . '|max:255',
            'no_hp' => 'nullable|string|max:20',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->username = $validated['username'];
        $user->no_hp = $validated['no_hp'] ?? null;

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $user->photo = $request->file('photo')->store('users', 'public');
        }

        $user->save();

        return back()->with('success', 'Profil admin berhasil diperbarui!');
    }

    /**
     * Tampilkan halaman pengaturan akun & keamanan
     */
    public function account()
    {
        return view('backend.admin.settings.account');
    }

    /**
     * Update password admin
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah!']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah!');
    }

    /**
     * Tampilkan halaman pengaturan usaha dan identitas website.
     */
    public function general()
    {
        $settings = DB::table('settings')->pluck('value', 'key')->all();
        $settings = array_merge([
            'nama_bengkel' => 'UD Rumah Daun',
            'alamat' => '',
            'telepon' => '',
            'email' => '',
            'jam_operasional' => '08:00 - 17:00',
            'deskripsi' => '',
            'site_logo' => null,
        ], $settings);

        return view('backend.admin.settings.general', compact('settings'));
    }

    /**
     * Update informasi usaha dan logo website.
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'nama_bengkel' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'jam_operasional' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $previousLogo = DB::table('settings')->where('key', 'site_logo')->value('value');
        if ($request->hasFile('site_logo')) {
            $validated['site_logo'] = $request->file('site_logo')->store('site', 'public');
        } else {
            unset($validated['site_logo']);
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated as $key => $value) {
                if ($value === null && $key !== 'site_logo') {
                    $value = '';
                }

                DB::table('settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
        });

        if (isset($validated['site_logo']) && $previousLogo) {
            Storage::disk('public')->delete($previousLogo);
        }

        return back()->with('success', 'Pengaturan usaha dan logo website berhasil diperbarui!');
    }

    /**
     * Tampilkan halaman pengaturan notifikasi
     */
    public function notification()
    {
        return view('backend.admin.settings.notification');
    }

    /**
     * Update pengaturan notifikasi
     */
    public function updateNotification(Request $request)
    {
        // Validasi dan simpan preferensi notifikasi
        return back()->with('success', 'Preferensi notifikasi berhasil disimpan!');
    }

    /**
     * Tampilkan halaman backup & restore
     */
    public function backup()
    {
        return view('backend.admin.settings.backup');
    }
}