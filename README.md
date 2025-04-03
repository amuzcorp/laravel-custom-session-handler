# Laravel Custom Session Handler

[![Latest Stable Version](https://poser.pugx.org/amuzcorp/laravel-custom-session-handler/v)](https://packagist.org/packages/amuzcorp/laravel-custom-session-handler)
[![License](https://poser.pugx.org/amuzcorp/laravel-custom-session-handler/license)](https://packagist.org/packages/amuzcorp/laravel-custom-session-handler)

Laravel의 기본 Database Session 드라이버를 확장하여  
**특정 라우트나 조건에 따라 `last_activity` 컬럼 업데이트를 제외할 수 있는 세션 핸들러**입니다.

---

## ✨ 주요 기능

- `SESSION_DRIVER=custom_database`로 간단히 사용 가능
- `request()->routeIs()` 기반으로 제외할 라우트 설정
- 사용자가 직접 `callback`을 등록해 제외 조건 제어 가능
- Laravel 9, 10, 11, 12 호환

---

## 💾 설치

```bash
composer require amuzcorp/laravel-custom-session-handler
```

---

## ⚙️ 설정

### 1. `.env`

```env
SESSION_DRIVER=custom_database
```

### 2. `config/session.php`

확인만 하면 됩니다. (`SESSION_DRIVER` 값을 가져오기 때문에 수정 불필요)

### 3. 설정 파일 퍼블리시 (선택사항)

필요한 경우 다음 명령어로 설정 파일을 퍼블리시할 수 있습니다:

```bash
php artisan vendor:publish --provider="Amuz\CustomSession\CustomSessionServiceProvider" --tag=config
```

---

## 🧩 자동 등록되는 Service Provider

패키지는 `CustomSessionServiceProvider`를 자동으로 등록합니다.  
서비스 프로바이더 내에서 커스텀 세션 핸들러가 다음과 같이 동작합니다:

```php
Session::extend('custom_database', function ($app) {
    $handler = new CustomDatabaseSessionHandler(
        DB::connection(config('session.connection')),
        config('session.table'),
        config('session.lifetime'),
        $app
    );

    // route 이름 기반으로 제외
    $handler->excludeRoutes([
        'health.check',
        'api.metrics',
    ]);

    // callback 기반으로 제외
    $handler->addExclusionCallback(function ($request) {
        return str_contains($request->userAgent(), 'HealthChecker');
    });

    return $handler;
});
```

---

## ✅ 예시: routes/web.php

```php
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
})->name('health.check');

Route::get('/dashboard', function () {
    session(['visited' => now()]); // 세션이 업데이트되어야 함
    return view('dashboard');
});
```

---

## 🧪 작동 방식

- `CustomDatabaseSessionHandler::write()`에서 `request()->routeIs()` 또는 등록된 콜백에 따라 `last_activity` 업데이트 여부 결정
- 제외 조건을 만족하면 `last_activity` 컬럼은 변경되지 않음
- 나머지 Laravel 세션 기능은 그대로 유지됨

---

## 📦 composer.json 참고

```json
"extra": {
  "laravel": {
    "providers": [
      "Amuzcorp\\CustomSession\\CustomSessionServiceProvider"
    ]
  }
}
```

---

## 🔐 보안 및 버전 정책

- Laravel 공식 `DatabaseSessionHandler`를 그대로 상속하므로 보안 및 세션 구조는 동일
- 버전은 Semantic Versioning(semver)을 따릅니다: `MAJOR.MINOR.PATCH`

---

## 🪪 라이선스

MIT License. 자유롭게 사용, 수정, 재배포 가능합니다.

---

## ✋ 기여하기

- PR / Issue 환영합니다
- 라우트 패턴 정규식 지원, exclude by middleware 등 확장 아이디어도 환영합니다!
