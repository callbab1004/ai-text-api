# AI Text API (Laravel + Docker)

간단한 **교정/번역 API** 예제 프로젝트. Docker 기반으로 어디서나 동일하게 실행됩니다.  
LLM은 `.env`에서 **더미 모드 ↔ OpenAI 실구현**을 스위치로 전환할 수 있습니다.

---

## ✨ Features
- Laravel 11 + PHP-FPM + Nginx + MySQL (Docker Compose)
- API Key 미들웨어(`X-API-KEY`)
- `/api/v1/text/transform` (correct/translate)
- **LLM_DUMMY=true**: 예시 2개만 하드코딩 응답  
  - `안녕하세요.` → `Hello`  
  - `공백 정리 테스트` → `공백정리테스트`
- **LLM_DUMMY=false**: OpenAI Responses API 호출

---

## 🚀 Quick Start (Docker)
```bash
git clone <THIS_REPO_URL>
cd ai-text-api

cp .env.example .env
docker compose up -d --build
# 의존성/키/마이그레이션/시더/헬스는 init 스크립트나 make로 처리
./bin/init.sh
# 또는 make가 있다면
# make dev
```

## 🩺 Health & Ping

### GET `/api/v1/health`
서비스/시간/버전 등 기본 상태 체크용 엔드포인트입니다.

**요청**
```bash
curl -i http://localhost:8080/api/v1/health

# 성공 예시
HTTP/1.1 200 OK
Content-Type: application/json

{
  "ok": true,
  "ts": "2025-10-15T21:00:00+09:00",
}
```

### GET `/api/v1/ping`

API Key 인증 및 라우팅/미들웨어 확인용 헬스+권한 체크.

**요청 (헤더에 API 키 필수)**
```bash
curl -i http://localhost:8080/api/v1/ping \
  -H "X-API-KEY: test_dev_key_1234567890"

# 성공 예시
HTTP/1.1 200 OK
Content-Type: application/json

{
  "pong": true,
  "ts": "2025-10-15T21:00:00+09:00"
}

# 인증 실패 (예시)
HTTP/1.1 401 Unauthorized
Content-Type: application/json

{
  "error": { "code": "UNAUTHORIZED", "message": "Invalid or missing API key" }
}
```

## 🧪 Transform 테스트
```bash
# translate (더미/실구현 동일)
curl -s -i -X POST http://localhost:8080/api/v1/text/transform \
  -H "X-API-KEY: test_dev_key_1234567890" \
  -H "Content-Type: application/json" \
  -d '{"mode":"translate","text":"안녕하세요.","target_lang":"en"}'

# correct
curl -s -i -X POST http://localhost:8080/api/v1/text/transform \
  -H "X-API-KEY: test_dev_key_1234567890" \
  -H "Content-Type: application/json" \
  -d '{"mode":"correct","text":"공백 정리 테스트"}'
```

## ⚙️ 환경 변수(.env)
```bash
APP_NAME=AI-Text-API
...
# LLM (더미 ↔ 실구현 스위치)
LLM_DUMMY=true
OPENAI_API_KEY=
OPENAI_API_BASE=https://api.openai.com/v1
LLM_MODEL=gpt-4o-mini
LLM_TIMEOUT=15
LLM_RETRIES=3
LLM_RETRY_BASE_MS=500
LLM_FALLBACK_DUMMY_ON_429=false

# 실구현을 쓰려면: LLM_DUMMY=false + OPENAI_API_KEY 설정(프로젝트 결제/Usage limit 필요).
```

## 🗄️ 데이터베이스
```bash
# 필요 테이블은 2개
- api_keys (시더로 테스트용키 test_dev_key_1234567890 주입)
- requests_log

# 마이그레이션, 시더 실행
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

## 🔌 포트/서비스
```bash
- Web (Nginx): http://localhost:8080
- App (PHP-FPM): 내부 컨테이너
- DB (MySQL): db:3306 (호스트에서 접속하려면 compose 설정에 따라 포트 매핑 ex.3307)
```

## ❗️Troubleshooting
```bash
401 Unauthorized → X-API-KEY 값/시더 키 확인
SQLSTATE[HY000] [2002] Connection refused → DB 기동/호스트 확인(DB_HOST=db), docker compose ps, logs web/app
General error: attempt to write a readonly database → storage, bootstrap/cache 권한
429 quota exceeded (OpenAI) → 프로젝트 결제/Usage limit 설정, LLM_DUMMY=true(테스트용), false(실연동)
라우트 확인: docker compose exec app php artisan route:list --path=v1
```

## 📜 License
MIT