<?php

namespace Abrahamyanvaxik\LaravelLoggable\Drivers;

class File extends LoggerDriver
{
    /**
     * Prepend the log text to the log file.
     * Create a new file if the file is not exist.
     *
     * @return void
     */
    public function prepend()
    {
        $model_name = str_replace('\\', '', $this->model_name);
        $storage_path = $this->config['storage_path']
            .'/'.$model_name
            .'/'.date('YF');

        $file_path = $storage_path.'/'.date('d').'.log';

        if (! \Illuminate\Support\Facades\File::exists($storage_path)) {
            mkdir($storage_path, 0755, true);
        }

        $text = $this->getLogTemplate();

        \Illuminate\Support\Facades\File::prepend($file_path, $text);
    }

    /**
     * Get the template for incoming action.
     *
     * @return string
     */
    private function getLogTemplate()
    {
        $user_id = $this->user ? $this->user->id : 'N/A';

        $template = now()->toDateTimeString().PHP_EOL;

        if ($this->action === 'create') {
            $template .= "New model was created by $user_id."
                .PHP_EOL.'Inserted data: '
                .PHP_EOL.http_build_query($this->model->toArray(), '', PHP_EOL);
        } elseif ($this->action === 'edit') {
            $template .= "Model {$this->model->id} was update by $user_id."
                .PHP_EOL.'MODEL BEFORE '
                .PHP_EOL.http_build_query(array_intersect_key($this->model->getOriginal(),
                    array_intersect_key($this->model->getChanges(),
                        array_flip($this->loggable_fields))), '', PHP_EOL)

                .PHP_EOL.'MODEL AFTER '
                .PHP_EOL.http_build_query(array_intersect_key($this->model->getChanges(),
                    array_flip($this->loggable_fields)), '', PHP_EOL);
        } elseif ($this->action === 'delete') {
            $template .= "Model {$this->model->id} was deleted by $user_id";
        }

        $template .= PHP_EOL.PHP_EOL;

        return $template;
    }
}
