<?php

return [

    /*
    |---------------------------------------------------------------------------
    | Temporary File Uploads
    |---------------------------------------------------------------------------
    |
    | Livewire menyimpan file upload sementara di direktori "livewire-tmp"
    | sebelum diproses. Aturan default Livewire hanya mengizinkan 12 MB,
    | sehingga perlu dinaikkan agar file GeoJSON/JSON hingga 100 MB
    | (102400 KB) dapat diunggah.
    |
    */

    'temporary_file_upload' => [
        'rules' => ['required', 'file', 'max:102400'],
    ],
];
