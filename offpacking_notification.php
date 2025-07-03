<?php

$data = file_get_contents('php://input');
if(!$data) response(400);
try {
	$json = json_decode($data);
	if(!$json) response(400, json_last_error_msg(), $data);
} catch(\Exception $e) {
	response(400, $e->getMessage(), $data);
}
if(!isset($json->mertid, $json->type, $json->code, $json->url, $json->_, $json->hash)) response(400, 'data missing', $json);
$time = time();
if($json->_ < $time - 60 || $json->_ > $time + 60) response(400, 'expired', $json);
$mertData = json_decode(file_get_contents('../configure/offpacking.json')); // 키 파일은 웹에서 접근할 수 없는 경로에 저장하세요.
if($json->mertid !== $mertData->mertid) response(400, 'invalid mertid', $json);
if($json->hash !== hash('sha256', $json->mertid.$json->type.$json->code.$json->url.$json->_.$mertData->secret)) response(400, 'hash mismatch', $json);

$db = new PDO(/* ... */);

// RETURNING 문법이 없는 DBMS 는 SELECT 후 UPDATE 로 하거나, UPDATE 되었는지 rowCount() affected_rows 등의 값으로 체크하세요.
switch($json->type) {
	case 'seq': // 고유번호
		if(!preg_match('~^[1-9]\d*$~', $json->code)) response(400, 'invalid seq', $json);
		$stmt = $db->prepare("UPDATE 물품정보 SET packingurl = :url WHERE seq = :seq RETURNING *");
		$stmt->execute(['url' => $json->url, 'seq' => $json->code]);
		if(!($row = $stmt->fetchObject())) {
			// 물품정보가 없는 경우 물품 자체가 누락된 것일 수 있으니 오류를 리턴하지 말고 따로 저장하세요.
			response(200, "고유번호 {$json->code} 가 없습니다.");
		}
		sendMail($row->no, "사이트명]고유번호 {$json->code} 의 포장 영상이 촬영되었습니다.", <<<TEXT
<p>고유번호 {$json->code} 의 포장 영상이 촬영되었습니다.</p>
<p>[<a href="{$json->url}" target="_blank">다운로드 하러가기</a>]</p>
TEXT
		);
		response(200);
		break;
	case 'bundle': // 묶음번호
		if(!preg_match('~^[1-9]\d*$~', $json->code)) response(400, 'invalid bundle', $json);
		$stmt = $db->prepare("UPDATE 물품정보 SET packingurl = :url WHERE bundle = :bundle AND 발송되지않은물품 RETURNING *");
		$stmt->execute(['url' => $json->url, 'bundle' => $json->code]);
		if(!($row = $stmt->fetchObject())) {
			// 물품정보가 없는 경우 물품 자체가 누락된 것일 수 있으니 오류를 리턴하지 말고 따로 저장하세요.
			response(200, "묶음번호 {$json->code} 가 없습니다.");
		}
		sendMail($row->no, "사이트명]묶음번호 {$json->code} 의 포장 영상이 촬영되었습니다.", <<<TEXT
<p>묶음번호 {$json->code} 의 포장 영상이 촬영되었습니다.</p>
<p>[<a href="{$json->url}" target="_blank">다운로드 하러가기</a>]</p>
TEXT
		);
		response(200);
		break;
	case 'trackingno': // 트래킹번호(송장번호)
		if(!preg_match('~^[a-z\d]+$~i', $json->code)) response(400, 'invalid trackingno', $json);
		$stmt = $db->prepare("UPDATE 물품정보 SET packingurl = :url WHERE trackingno = :trackingno AND 발송되지않은물품 RETURNING *");
		$stmt->execute(['url' => $json->url, 'trackingno' => $json->code]);
		if(!($row = $stmt->fetchObject())) {
			// 물품정보가 없는 경우 물품 자체가 누락된 것일 수 있으니 오류를 리턴하지 말고 따로 저장하세요.
			response(200, "송장번호 {$json->code} 가 없습니다.");
		};
		sendMail($row->no, "사이트명]송장번호 {$json->code} 의 포장 영상이 촬영되었습니다.", <<<TEXT
<p>송장번호 {$json->code} 의 포장 영상이 촬영되었습니다.</p>
<p>[<a href="{$json->url}" target="_blank">다운로드 하러가기</a>]</p>
TEXT
		);
		response(200);
		break;
	default:
		response(400, 'invalid type', $json);
}

function response($status, $message = '', $data = []) {
	header('Content-Type: application/json; charset=utf-8');
	// 응답 메세지는 오프패킹 관리자에서 확인할 수 있습니다.
	echo json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
}

function sendMail($no, $subject, $contents) {
	global $db;
	// 업로드가 완료되면 고객에게 메일을 발송하세요.
}