<?php

namespace App\Http\Controllers;

use Helper;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class AutoTranslateAttributesController extends Controller
{
    public static function AutoTranslateAttributes($model, $fields, $command) {

        $fullModelClass = 'App\\Models\\' . $model;

        try {
            $sourceData = (new $fullModelClass)::select(['id', ...$fields])->where('is_translated',0)->get();

            $targetLangs = Helper::getMultiLang();

            foreach ($sourceData as $item) {
                $languageData = [];

                foreach ($targetLangs as $lang) {
                    if ($lang === 'en') continue; 

                    $translator = new GoogleTranslate($lang, 'en');

                    foreach ($fields as $fieldName) {
                        $original = $item->$fieldName;

                        if (!$original) continue;
                        if ($fieldName == 'id') continue;

                        try {
                            $translated = $translator->translate($original);
                            // $languageData[$lang]['id'] = $item->id;
                            $languageData[$lang][$fieldName] = $translated;

                            $command->info("[ID {$item->id}] [$lang] $fieldName => $translated");
                        } catch (\Throwable $e) {
                            $logDir = storage_path('logs');
                            $logFile = $logDir . '/transtaion-errors.log';

                            if (!file_exists($logDir)) {
                                mkdir($logDir, 0755, true);
                            }

                            $logString = "[" . now() . "] ID {$item->id} | Lang: $lang | Field: $fieldName | Error: " . $e->getMessage() . "\n";

                            file_put_contents($logFile, $logString, FILE_APPEND);

                            $command->error("Translation error (ID {$item->id} - $lang - $fieldName): " . $e->getMessage());
                        }
                    }
                }

                $item->is_translated = 1;
                $item->languages = json_encode($languageData, JSON_PRETTY_PRINT);
                $item->save();

                $command->info("Updated ID {$item->id} with translated language data.");
            }

        } catch (\Throwable $e) {
            $logDir = storage_path('logs');
            $logFile = $logDir . '/transtaion-errors.log';

            if (!file_exists($logDir)) {
                mkdir($logDir, 0755, true);
            }

            $logString = "[" . now() . "] Command Error: " . $e->getMessage() . "\n";

            file_put_contents($logFile, $logString, FILE_APPEND);
            $command->error("Command Error: " . $e->getMessage());
        }

    }
}
