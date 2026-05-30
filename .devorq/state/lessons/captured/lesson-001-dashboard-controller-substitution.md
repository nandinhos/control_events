# Lesson 001 — Dashboard Livewire sem mount() exige Controller

## Contexto
- **Projeto:** control_events
- **Data:** 2025-05-30
- **Stack:** Laravel 13 + Livewire v4.3.0 + Flux Pro v2.14

## Problema
Ao usar `app(Livewire\Component::class)` para renderizar Dashboard via route, o Livewire NAO executa o metodo `mount()` do componente. Isso significa que qualquer lógica de inicialização (queries no BD, cálculos, etc.) dentro de `mount()` é completamente ignorada.

## Solução Definitiva
Criar um **Controller** dedicado para o dashboard que chama explicitamente o componente Livewire apos executar a lógica de preparação:

```php
// routes/web.php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// app/Http/Controllers/DashboardController.php
class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'stats' => DashboardStats::fromCache(),
        ]);
    }
}
```

**Regra:** NUNCA use `app(Livewire\Component::class)` diretamente em rotas quando o componente tem lógica de inicialização em `mount()`. Use um Controller intermediary.

## Tags
#laravel #livewire #dashboard #architecture
