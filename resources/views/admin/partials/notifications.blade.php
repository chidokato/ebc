@php
    $notifications = [];
    foreach (['success' => 'Thành công', 'error' => 'Có lỗi xảy ra', 'warning' => 'Lưu ý', 'info' => 'Thông báo'] as $type => $heading) {
        if (session($type)) $notifications[] = ['type' => $type, 'heading' => $heading, 'messages' => [session($type)]];
    }
    if ($errors->any()) $notifications[] = ['type' => 'error', 'heading' => 'Vui lòng kiểm tra thông tin', 'messages' => $errors->all()];
@endphp
@if (count($notifications))
<style>
    .admin-notifications{position:fixed;left:20px;bottom:20px;z-index:1090;width:380px;max-width:calc(100vw - 32px);max-height:calc(100vh - 40px);overflow-y:auto;pointer-events:none}
    .admin-notification.toast{width:100%;opacity:1;background:#fff;border:0;border-left:4px solid var(--notification-color);border-radius:10px;box-shadow:0 8px 30px rgba(22,34,60,.2);pointer-events:auto}
    .admin-notification + .admin-notification{margin-top:12px}
    .admin-notification .toast-body{display:flex;align-items:flex-start;gap:12px;padding:16px;color:#344054;overflow-wrap:anywhere}
    .admin-notification-icon{color:var(--notification-color);font-size:23px;line-height:1.2}
    .admin-notification .btn-close{flex-shrink:0;margin-left:auto}
    @media(max-width:575px){.admin-notifications{left:16px;bottom:16px}}
</style>
<div class="admin-notifications" aria-label="Thông báo">
    @foreach ($notifications as $notification)
    <div class="toast show admin-notification" style="--notification-color:{{ ['success' => '#099885', 'error' => '#d64550', 'warning' => '#b7791f', 'info' => '#405189'][$notification['type']] }}" role="{{ $notification['type'] === 'error' ? 'alert' : 'status' }}" aria-live="{{ $notification['type'] === 'error' ? 'assertive' : 'polite' }}" aria-atomic="true" data-bs-autohide="{{ in_array($notification['type'], ['error', 'warning']) ? 'false' : 'true' }}" data-bs-delay="6000">
        <div class="toast-body">
            <i class="admin-notification-icon {{ $notification['type'] === 'success' ? 'ri-checkbox-circle-fill' : 'ri-information-fill' }}" aria-hidden="true"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">{{ $notification['heading'] }}</strong>
                @foreach ($notification['messages'] as $message)<div>{{ $message }}</div>@endforeach
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Đóng thông báo"></button>
        </div>
    </div>
    @endforeach
</div>
@endif
