<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token; // [QUAN TRỌNG] Khai báo public để View đọc được

    // Constructor: Nơi nhận hàng
    public function __construct($token)
    {
        $this->token = $token;
    }

    public function build() // Envelope (phong bì) và Content (nội dung)
    {
        return $this->subject('Yêu cầu đặt lại mật khẩu') // Tiêu đề mail
                    ->view('emails.reset_password');      // Trỏ đến file Blade
    }
}
