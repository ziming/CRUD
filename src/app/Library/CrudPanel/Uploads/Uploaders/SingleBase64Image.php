<?php

namespace Backpack\CRUD\app\Library\CrudPanel\Uploads\Uploaders;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SingleBase64Image extends Uploader
{
    public function uploadFile(Model $entry, $value = null)
    {
        $value = $value ?? CRUD::getRequest()->get($this->name);
        $previousImage = $entry->getOriginal($this->name);

        if (! $value && $previousImage) {
            Storage::disk($this->disk)->delete($previousImage);

            return null;
        }

        if (Str::startsWith($value, 'data:image')) {
            if ($previousImage) {
                Storage::disk($this->disk)->delete($previousImage);
            }

            $base64Image = Str::after($value, ';base64,');

            $finalPath = $this->path.$this->getFileNameWithExtension($value);

            Storage::disk($this->disk)->put($finalPath, base64_decode($base64Image));

            return $finalPath;
        }

        return $previousImage;
    }

    public function saveRepeatableFile(Model $entry, $value = null)
    {
        $previousImages = $this->getPreviousRepeatableValues($entry);

        foreach ($value as $row => $rowValue) {
            if ($rowValue) {
                if (Str::startsWith($rowValue, 'data:image')) {
                    $base64Image = Str::after($rowValue, ';base64,');
                    $finalPath = $this->path.$this->getFileNameWithExtension($rowValue);
                    Storage::disk($this->disk)->put($finalPath, base64_decode($base64Image));
                    $value[$row] = $previousImages[] = $finalPath;

                    continue;
                }
            }
        }

        $imagesToDelete = array_diff($previousImages, $value);

        foreach ($imagesToDelete as $image) {
            Storage::disk($this->disk)->delete($image);
        }

        return $value;
    }
}
