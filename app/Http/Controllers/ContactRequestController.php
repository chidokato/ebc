<?php

namespace App\Http\Controllers;

use App\Mail\ContactRequestMail;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class ContactRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'service' => ['required', Rule::in(['Đặt lịch tham quan', 'Tổ chức sự kiện', 'Đăng ký hội viên'])],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:filter', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^[+0-9 ().-]{8,20}$/'],
            'message' => ['required', 'string', 'max:5000'],
        ], [], [
            'service' => 'dịch vụ yêu cầu', 'name' => 'họ và tên',
            'email' => 'email', 'phone' => 'số điện thoại', 'message' => 'chi tiết yêu cầu',
        ]);

        $recipient = WebsiteSetting::current()->booking_email ?: config('mail.booking_to');
        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)
            || in_array(config('mail.default'), ['log', 'array'], true)) {
            return response()->json(['message' => 'Chức năng gửi yêu cầu đang được cấu hình. Vui lòng gọi 0986 003 663 để được hỗ trợ.'], 503);
        }

        try {
            Mail::to($recipient)->send(new ContactRequestMail($data));
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Chưa gửi được yêu cầu. Vui lòng thử lại sau hoặc gọi 0986 003 663.'], 503);
        }

        return response()->json(['message' => 'Đã gửi yêu cầu liên hệ. Elite Business Center sẽ liên hệ với bạn để tư vấn.']);
    }
}
