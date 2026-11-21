<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoaiSanPham;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Gloudemans\Shoppingcart\Facades\Cart;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class HomeController extends Controller
{
    public function getHome()
    {
        $loaisanpham = LoaiSanPham::with([
            'SanPham' => function ($query) {
                $query->latest()->take(8);
            }
        ])->get();

        return view('frontend.home', compact('loaisanpham'));
    }

    public function getSanPham(string $tenloai_slug = '')
    {
        // Bổ sung code tại đây
        return view('frontend.sanpham');
    }

    public function getSanPham_ChiTiet(string $tenloai_slug = '', string $tensanpham_slug = '')
    {
        // Bổ sung code tại đây
        return view('frontend.sanpham_chitiet');
    }

    public function getBaiViet(string $tenchude_slug = '')
    {
        // Bổ sung code tại đây
        return view('frontend.baiviet');
    }

    public function getBaiViet_ChiTiet(string $tenchude_slug = '', string $tieude_slug = '')
    {
        // Bổ sung code tại đây
        return view('frontend.baiviet_chitiet');
    }

    public function getGioHang()
    {
        if (Cart::count() > 0)
            return view('frontend.giohang');
        else
            return view('frontend.giohangrong');
    }

    public function getGioHang_Them(string $tensanpham_slug = '')
    {
        $sanpham = SanPham::where('tensanpham_slug', $tensanpham_slug)->first();

        Cart::add(
            $sanpham->id,
            $sanpham->tensanpham,
            1,
            $sanpham->dongia,
            0,
            ['image' => $sanpham->hinhanh]
        );

        return redirect()->route('frontend.home');
    }

    public function getGioHang_Xoa(string $row_id)
    {
        Cart::remove($row_id);
        return redirect()->route('frontend.giohang');
    }

    public function getGioHang_Giam(string $row_id)
    {
        $row = Cart::get($row_id);

        if ($row->qty > 1) {
            Cart::update($row_id, $row->qty - 1);
        }

        return redirect()->route('frontend.giohang');
    }

    public function getGioHang_Tang(string $row_id)
    {
        $row = Cart::get($row_id);

        if ($row->qty < 10) {
            Cart::update($row_id, $row->qty + 1);
        }

        return redirect()->route('frontend.giohang');
    }

    public function getTuyenDung()
    {
        return view('frontend.tuyendung');
    }

    public function getLienHe()
    {
        return view('frontend.lienhe');
    }

    public function getDangKy()
    {
        return view('user.dangky');
    }

    public function getDangNhap()
    {
        return view('user.dangnhap');
    }

    public function getGoogleLogin()
    {
        return Socialite::driver('google')->redirect();
    }

    public function getGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')
                ->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))
                ->stateless()
                ->user();
        } catch (Exception $e) {
            return redirect()->route('user.dangnhap')->with('warning', 'Lỗi xác thực. Xin vui lòng thử lại!');
        }

        $existingUser = User::where('email', $user->email)->first();
        if ($existingUser) {
            Auth::login($existingUser, true);
            return redirect()->route('user.home');
        } else {
            $newUser = User::create([
                'name' => $user->name,
                'email' => $user->email,
                'username' => Str::before($user->email, '@'),
                'password' => Hash::make('larashop@2025'),
            ]);

            Auth::login($newUser, true);
            return redirect()->route('user.home');
        }
    }
}