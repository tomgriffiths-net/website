robocopy ..\..\mywebsite\website        files\webfiles.zip        /e /v /mir /xd files
robocopy ..\..\mywebsite\website\files  files\webfiles-files.zip  /e /v /mir
robocopy ..\..\mywebsite\localfiles     files\webfiles-local.zip  /e /v /mir