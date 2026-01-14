<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$API_KEY = "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";
$MODEL = "stabilityai/stable-diffusion-xl-base-1.0";

$input = json_decode(file_get_contents("php://input"), true);
$prompt = $input["prompt"] ?? "";

if (!$prompt) {
  echo json_encode(["error" => "Prompt missing"]);
  exit;
}

$payload = json_encode([
  "inputs" => $prompt,
  "options" => ["wait_for_model" => true]
]);

$ch = curl_init("https://api-inference.huggingface.co/models/$MODEL");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer $API_KEY",
  "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
  echo json_encode([
    "error" => "AI busy or failed",
    "status" => $httpCode
  ]);
  exit;
}

$imageBase64 = base64_encode($response);

echo json_encode([
  "success" => true,
  "image" => "data:image/png;base64," . $imageBase64
]);