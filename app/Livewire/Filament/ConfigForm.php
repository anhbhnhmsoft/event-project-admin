<?php

namespace App\Livewire\Filament;

use App\Services\ConfigService;
use Filament\Notifications\Notification;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Utils\Constants\StoragePath;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ConfigForm extends Component
{
    use WithFileUploads;

    private ConfigService $service;

    public array $config_value = [];
    public array $organizer_value = [];

    public $configList;
    public bool $isSuperAdmin = false;
    public mixed $organizerId = null;
    public $organizer;

    public function boot(ConfigService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->configList = $this->service->getAllConfigByOrganizerId($this->organizerId);

        foreach ($this->configList as $config) {
            $this->config_value[$config->config_key] = $config->config_value;
        }

        if (!$this->isSuperAdmin) {
            $this->organizer = $this->service->getOrganizerInfo($this->organizerId);

            // Khởi tạo giá trị organizer
            $this->organizer_value = [
                $this->organizer->name => $this->organizer->name,
                'image' => null, // File upload sẽ được bind sau
                'description' => $this->organizer->description ?? '',
            ];
        }
    }

    public function updateConfig()
    {
        if (Arr::exists($this->config_value, 'LOGO') && $this->config_value['LOGO'] instanceof TemporaryUploadedFile) {
            $storedPath = $this->config_value['LOGO']->store(StoragePath::CONFIG_PATH->value, 'public');
            $this->config_value['LOGO'] = $storedPath;
        }

        $result = $this->service->updateConfigs($this->config_value);

        if ($result) {
            Notification::make()
                ->title('Cập nhật config thành công')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Cập nhật config thất bại')
                ->danger()
                ->send();
        }
    }

    public function updateConfigAdmin()
    {
        try {
            // Validate dữ liệu
            $this->validate([
                'organizer_value.' . $this->organizer->name => 'required|string|max:255',
                'organizer_value.description' => 'nullable|string',
                'organizer_value.image' => 'nullable|image|max:2048', // 2MB max
            ], [
                'organizer_value.' . $this->organizer->name . '.required' => 'Tên tổ chức không được để trống',
                'organizer_value.image.image' => 'File phải là hình ảnh',
                'organizer_value.image.max' => 'Kích thước ảnh tối đa 2MB',
            ]);

            // Chuẩn bị dữ liệu cập nhật organizer
            $organizerData = [
                'name' => $this->organizer_value[$this->organizer->name],
                'description' => $this->organizer_value['description'] ?? '',
            ];

            // Xử lý upload ảnh organizer
            if (isset($this->organizer_value['image']) && $this->organizer_value['image'] instanceof TemporaryUploadedFile) {
                // Xóa ảnh cũ nếu có
                if ($this->organizer->image && Storage::disk('public')->exists($this->organizer->image)) {
                    Storage::disk('public')->delete($this->organizer->image);
                }

                // Lưu ảnh mới
                $storedPath = $this->organizer_value['image']->store(StoragePath::ORGANIZER_PATH->value ?? 'organizers', 'public');
                $organizerData['image'] = $storedPath;
            }

            // Cập nhật thông tin organizer
            $organizerUpdated = $this->service->updateOrganizer($this->organizerId, $organizerData);

            // Xử lý LOGO trong config nếu có
            if (Arr::exists($this->config_value, 'LOGO') && $this->config_value['LOGO'] instanceof TemporaryUploadedFile) {
                $storedPath = $this->config_value['LOGO']->store(StoragePath::CONFIG_PATH->value, 'public');
                $this->config_value['LOGO'] = $storedPath;
            }

            // Cập nhật configs
            $configUpdated = $this->service->updateConfigsByOrganizerId($this->organizerId, $this->config_value);

            if ($organizerUpdated && $configUpdated) {
                // Refresh dữ liệu
                $this->organizer = $this->service->getOrganizerInfo($this->organizerId);

                Notification::make()
                    ->title('Cập nhật thành công')
                    ->body('Thông tin tổ chức và cấu hình đã được cập nhật')
                    ->success()
                    ->send();

                // Reset file input
                $this->organizer_value['image'] = null;
            } else {
                throw new \Exception('Cập nhật thất bại');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Cập nhật thất bại')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.filament.config-form');
    }
}
