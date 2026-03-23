<?php
/**
 * OFTK – Contact form handler. Sends message to info@oftk-ks.org.
 */
header('Content-Type: application/json; charset=utf-8');

$to = 'info@oftk-ks.org';
$siteName = 'OFTK – Oda e Fizioterapeutëve të Kosovës';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Metoda e lejuar: POST.']);
  exit;
}

$name    = isset($_POST['name'])    ? trim((string) $_POST['name'])    : '';
$email   = isset($_POST['email'])  ? trim((string) $_POST['email'])   : '';
$subject = isset($_POST['subject']) ? trim((string) $_POST['subject']) : '';
$message = isset($_POST['message']) ? trim((string) $_POST['message']) : '';

if ($name === '') {
  echo json_encode(['ok' => false, 'message' => 'Ju lutem vendosni emrin dhe mbiemrin.']);
  exit;
}
if ($email === '') {
  echo json_encode(['ok' => false, 'message' => 'Ju lutem vendosni email-in.']);
  exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['ok' => false, 'message' => 'Email-i nuk është i vlefshëm.']);
  exit;
}
if ($message === '') {
  echo json_encode(['ok' => false, 'message' => 'Ju lutem shkruani mesazhin.']);
  exit;
}

$emailSubject = $subject !== '' ? $subject : 'Mesazh nga faqja e kontaktit – OFTK';
$body = "Mesazh nga formulari i kontaktit të " . $siteName . "\n\n";
$body .= "Emri dhe mbiemri: " . $name . "\n";
$body .= "Email: " . $email . "\n";
if ($subject !== '') {
  $body .= "Subjekti: " . $subject . "\n";
}
$body .= "\n--- Mesazhi ---\n\n" . $message . "\n";

$headers = [
  'From: ' . $siteName . ' <' . $to . '>',
  'Reply-To: ' . $email,
  'X-Mailer: PHP/' . phpversion(),
  'Content-Type: text/plain; charset=UTF-8',
  'MIME-Version: 1.0',
];

$subjectEncoded = '=?UTF-8?B?' . base64_encode($emailSubject) . '?=';
$sent = @mail($to, $subjectEncoded, $body, implode("\r\n", $headers));

if ($sent) {
  echo json_encode([
    'ok' => true,
    'message' => 'Faleminderit! Mesazhi juaj u dërgua në info@oftk-ks.org. Do të ju përgjigjemi sa më shpejt.',
  ]);
} else {
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'message' => 'Mesazhi nuk u dërgua për shkak të një gabimi teknik. Ju lutem provoni me email direkt: info@oftk-ks.org ose telefon: +383 45 460 551.',
  ]);
}
