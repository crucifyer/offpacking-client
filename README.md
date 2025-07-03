# 오프패킹 사이트 구현 예제

* 저희 솔루션은 촬영 후 운영서버에 업로드 되기까지 약간의 시간이 소요되며, 업로드 완료 후 알림(notification) 을 보내어 처리하도록 되어 있습니다.
* 오프패킹 관리자에서 업로드 현황, 오류메세지 등을 확인하실 수 있습니다.

```html
<!-- 운영하시는 사이트 관리자에서 해당물품에 대한 링크를 걸어 촬영도 가능합니다. -->
<a href="https://고객사--사이트--전용.offpacking.kr/record.php?type=trackingno&code=7102383XXXXXX" target="_blank">7102383XXXXXX 물품 오프패킹 촬영하기</a>
<a href="https://고객사--사이트--전용.offpacking.kr/record.php?type=bundle&code=82734" target="_blank">묶음 82734 오프패킹 촬영하기</a>

<!-- 관리자에서 바코드 인식, 번호기입 등의 방식으로 일괄 촬영이 가능합니다. -->
<a href="https://고객사--사이트--전용.offpacking.kr/record.php" target="_blank">오프패킹으로 일괄 촬영 후 noti 온 것으로 판별하기</a>
```

* 지원 type

| 종류 | type | code |
| - | - | - |
| 고유번호 | seq | 숫자만 허용 |
| 묶음번호 | bundle | 영문대문자, 숫자만 허용 |
| 트래킹번호 | tracnkingno | 영문대문자, 숫자만 허용 |
 

https://offpacking.kr/