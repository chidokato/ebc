<?php

namespace App\Http\Controllers;

use App\Mail\BookingRequestMail;
use App\Models\WebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingRequestController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[+0-9 ().-]{8,20}$/'],
            'guests' => ['required', 'integer', 'min:1', 'max:100000'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ]);

        $recipient = WebsiteSetting::current()->booking_email ?: config('mail.booking_to');

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)
            || in_array(config('mail.default'), ['log', 'array'], true)) {
            return response()->json(['message' => 'Chức năng gửi yêu cầu đang được cấu hình. Vui lòng gọi 0986 003 663 để đặt lịch.'], 503);
        }

        try {
            Mail::to($recipient)->send(new BookingRequestMail($data));
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Chưa gửi được yêu cầu. Vui lòng thử lại sau hoặc gọi 0986 003 663.'], 503);
        }

        return response()->json(['message' => 'Đã gửi yêu cầu đặt lịch. Elite Business Center sẽ liên hệ với bạn để xác nhận.']);
    }
}
