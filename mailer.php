<?php
/**
 * Malatya Mobilya - SMTP Form Mailer
 * Teklif ve İletişim formlarından gelen başvuruları e-posta ile iletir.
 * PHPMailer kütüphanesi gerektirir: composer require phpmailer/phpmailer
 */

// CORS Header (gerekirse)
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://www.malatyamobilya.com');
header('Access-Control-Allow-Methods: POST');

// Sadece POST isteği kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Yalnızca POST isteği kabul edilir.']);
    exit;
}

// =============================================
// SMTP AYARLARI - Değiştirin!
// =============================================
define('SMTP_HOST',     'mail.firmaadi.com');   // SMTP Sunucu
define('SMTP_PORT',     587);                    // 587 (TLS) veya 465 (SSL)
define('SMTP_USER',     'info@firmaadi.com');    // SMTP Kullanıcı Adı
define('SMTP_PASS',     'SMTP_SIFRENIZ');        // SMTP Şifresi
define('SMTP_FROM',     'info@firmaadi.com');    // Gönderen Adres
define('SMTP_FROM_NAME','Malatya Mobilya Web Formu');

// Alıcı Adresleri
define('MAIL_TO_1', 'info@firmaadi.com');
define('MAIL_TO_2', 'teklif@firmaadi.com');
// =============================================

// Form verilerini al ve temizle
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

$name    = clean($_POST['name']    ?? '');
$phone   = clean($_POST['phone']   ?? '');
$email   = clean($_POST['email']   ?? '');
$city    = clean($_POST['city']    ?? '');
$service = clean($_POST['service'] ?? '');
$detail  = clean($_POST['detail']  ?? '');
$subject = clean($_POST['subject'] ?? 'Web Formu Başvurusu');
$message = clean($_POST['message'] ?? $detail);
$formType = clean($_POST['form_type'] ?? 'teklif'); // 'teklif' veya 'iletisim'

// Zorunlu alan kontrolü
if (empty($name) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ad ve telefon zorunludur.']);
    exit;
}

// PHPMailer kullanımı
// composer require phpmailer/phpmailer
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // SMTP Ayarları
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    // Gönderen
    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addReplyTo($email ?: SMTP_FROM, $name);

    // Alıcılar
    $mail->addAddress(MAIL_TO_1);
    if (MAIL_TO_2 !== MAIL_TO_1) {
        $mail->addAddress(MAIL_TO_2);
    }

    // Konu
    $emailSubject = $formType === 'teklif'
        ? "Yeni Teklif Talebi: {$name} — {$service}"
        : "Yeni İletişim Mesajı: {$name} — {$subject}";
    $mail->Subject = $emailSubject;

    // HTML İçerik
    $mail->isHTML(true);
    $mail->Body = "
    <!DOCTYPE html>
    <html lang='tr'>
    <head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'></head>
    <body style='font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px;'>
      <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1);'>
        
        <div style='background: linear-gradient(135deg, #2C2C2C 0%, #3D3D3D 100%); padding: 28px 32px; text-align: center;'>
          <h1 style='color: #C8A97A; font-family: Georgia, serif; margin: 0; font-size: 1.4rem;'>🪵 Malatya Mobilya</h1>
          <p style='color: rgba(255,255,255,0.6); font-size: 0.82rem; margin: 6px 0 0;'>
            " . ($formType === 'teklif' ? '🔔 Yeni Teklif Talebi Geldi' : '📩 Yeni İletişim Mesajı') . "
          </p>
        </div>

        <div style='padding: 32px;'>
          <h2 style='color: #2C2C2C; font-size: 1.1rem; margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid #f0ede8;'>
            Başvuru Detayları
          </h2>
          
          <table style='width: 100%; border-collapse: collapse;'>
            <tr style='border-bottom: 1px solid #f0ede8;'>
              <td style='padding: 10px 0; color: #9E9488; font-size: 0.82rem; width: 35%; text-transform: uppercase; letter-spacing: 0.06em;'>Ad Soyad</td>
              <td style='padding: 10px 0; color: #2C2C2C; font-weight: 600;'>{$name}</td>
            </tr>
            <tr style='border-bottom: 1px solid #f0ede8;'>
              <td style='padding: 10px 0; color: #9E9488; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.06em;'>Telefon</td>
              <td style='padding: 10px 0;'><a href='tel:{$phone}' style='color: #A0784A; font-weight: 600;'>{$phone}</a></td>
            </tr>
            " . ($email ? "<tr style='border-bottom: 1px solid #f0ede8;'>
              <td style='padding: 10px 0; color: #9E9488; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.06em;'>E-Posta</td>
              <td style='padding: 10px 0;'><a href='mailto:{$email}' style='color: #A0784A;'>{$email}</a></td>
            </tr>" : '') . "
            " . ($city ? "<tr style='border-bottom: 1px solid #f0ede8;'>
              <td style='padding: 10px 0; color: #9E9488; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.06em;'>İl / İlçe</td>
              <td style='padding: 10px 0; color: #2C2C2C;'>{$city}</td>
            </tr>" : '') . "
            " . ($service ? "<tr style='border-bottom: 1px solid #f0ede8;'>
              <td style='padding: 10px 0; color: #9E9488; font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.06em;'>Hizmet Türü</td>
              <td style='padding: 10px 0;'><span style='background: rgba(200,169,122,.15); color: #7A5530; padding: 3px 10px; border-radius: 50px; font-size: 0.82rem; font-weight: 600;'>{$service}</span></td>
            </tr>" : '') . "
          </table>

          " . (!empty($message) ? "
          <div style='margin-top: 20px; padding: 16px; background: #f8f5f0; border-radius: 8px; border-left: 3px solid #A0784A;'>
            <p style='color: #9E9488; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.06em; margin: 0 0 8px;'>Mesaj / Proje Detayı</p>
            <p style='color: #2C2C2C; line-height: 1.6; margin: 0; font-size: 0.92rem;'>{$message}</p>
          </div>" : '') . "

          <div style='margin-top: 28px; padding: 16px 20px; background: #1A1A1A; border-radius: 8px; display: flex; gap: 12px; align-items: center;'>
            <div>
              <p style='color: rgba(255,255,255,0.5); font-size: 0.75rem; margin: 0 0 4px;'>Bu müşteriye hızlıca ulaşın:</p>
              <a href='tel:{$phone}' style='color: #C8A97A; font-weight: 600; font-size: 1rem; text-decoration: none;'>📞 {$phone}</a>
              " . ($email ? "<a href='mailto:{$email}' style='display:block;color: rgba(255,255,255,0.5); font-size: 0.82rem; margin-top: 4px; text-decoration:none;'>✉ {$email}</a>" : '') . "
            </div>
          </div>
        </div>

        <div style='padding: 16px 32px; background: #f8f5f0; text-align: center; border-top: 1px solid #e0d9d0;'>
          <p style='color: #9E9488; font-size: 0.75rem; margin: 0;'>
            Bu e-posta <strong>malatyamobilya.com</strong> web sitesi üzerinden otomatik gönderilmiştir.
            Tarih: " . date('d.m.Y H:i') . "
          </p>
        </div>

      </div>
    </body>
    </html>";

    // Plain text alternatif
    $mail->AltBody = "Yeni Başvuru\n\nAd: {$name}\nTelefon: {$phone}\nE-posta: {$email}\nİl/İlçe: {$city}\nHizmet: {$service}\nMesaj: {$message}\n\nTarih: " . date('d.m.Y H:i');

    // Dosya ekleri (varsa)
    if (!empty($_FILES['files'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        foreach ($_FILES['files']['tmp_name'] as $i => $tmpPath) {
            if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                $fileType = $_FILES['files']['type'][$i];
                $fileSize = $_FILES['files']['size'][$i];
                $fileName = basename($_FILES['files']['name'][$i]);
                
                if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                    $mail->addAttachment($tmpPath, $fileName);
                }
            }
        }
    }

    $mail->send();

    // Kullanıcıya otomatik yanıt gönder
    if (!empty($email)) {
        $autoReply = new PHPMailer(true);
        $autoReply->isSMTP();
        $autoReply->Host       = SMTP_HOST;
        $autoReply->SMTPAuth   = true;
        $autoReply->Username   = SMTP_USER;
        $autoReply->Password   = SMTP_PASS;
        $autoReply->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $autoReply->Port       = SMTP_PORT;
        $autoReply->CharSet    = 'UTF-8';
        
        $autoReply->setFrom(SMTP_FROM, 'Malatya Mobilya');
        $autoReply->addAddress($email, $name);
        $autoReply->Subject = 'Başvurunuz Alındı — Malatya Mobilya';
        $autoReply->isHTML(true);
        $autoReply->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; background: white; padding: 32px; border-radius: 12px;'>
          <h2 style='color: #2C2C2C; font-family: Georgia, serif;'>🪵 Malatya Mobilya</h2>
          <p>Sayın <strong>{$name}</strong>,</p>
          <p>Başvurunuz başarıyla alınmıştır. <strong>24 saat içinde</strong> sizi arayacağız.</p>
          <p>Acil durumlar için: <a href='tel:+905551112233' style='color: #A0784A; font-weight: 600;'>0555 111 22 33</a></p>
          <p>WhatsApp: <a href='https://wa.me/905551112233' style='color: #25D366;'>Mesaj Gönder</a></p>
          <hr style='border: 1px solid #f0ede8; margin: 20px 0;'>
          <p style='color: #9E9488; font-size: 0.82rem;'>Malatya Mobilya — Çarşıbaşı Mah. No:15, Yeşilyurt / Malatya</p>
        </div>";
        
        try { $autoReply->send(); } catch (Exception $e) { /* Auto-reply başarısız olursa sessizce devam et */ }
    }

    echo json_encode(['success' => true, 'message' => 'Başvurunuz başarıyla alındı.']);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'E-posta gönderilemedi. Lütfen telefonla arayın.']);
}
?>
