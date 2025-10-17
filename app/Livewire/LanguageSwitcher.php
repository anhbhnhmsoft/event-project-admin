<?php

namespace App\Livewire;

use Illuminate\Support\Facades\App;
use Livewire\Component;

class LanguageSwitcher extends Component
{
    /**
     * Phương thức xử lý sự kiện chuyển đổi ngôn ngữ.
     * @param string $locale Mã ngôn ngữ (ví dụ: 'vi', 'en').
     */
    public function switchLanguage(string $locale): void
    {
        session(['locale' => $locale]);

        App::setLocale($locale);

        $this->redirect(request()->header('Referer'), navigate: false);
    }

    public function render()
    {
        return view('filament.hooks.lang-switcher');
    }
}
