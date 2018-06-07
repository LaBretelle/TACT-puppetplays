<?php

namespace App\Service;

class AppEnums
{
    // ProjectStatuses
    public const PROJECT_STATUS_FINISHED_DESC = 'project_status_finished_desc';
    public const PROJECT_STATUS_FINISHED_NAME = 'project_status_finished_name';
    public const PROJECT_STATUS_IN_PROGRESS_DESC = 'project_status_in_progress_desc';
    public const PROJECT_STATUS_IN_PROGRESS_NAME = 'project_status_in_progress_name';
    public const PROJECT_STATUS_NEW_DESC = 'project_status_new_desc';
    public const PROJECT_STATUS_NEW_NAME = 'project_status_new_name';

    // TranscriptionStatuses
    public const TRANSCRIPTION_STATUS_IN_PROGRESS = 'transcription_status_in_progress';
    public const TRANSCRIPTION_STATUS_IN_REREAD = 'transcription_status_in_reread';
    public const TRANSCRIPTION_STATUS_NONE = 'transcription_status_none';
    public const TRANSCRIPTION_STATUS_VALIDATED = 'transcription_status_validated';

    // ProjectUserStatuses
    public const USER_STATUS_MANAGER_DESC = 'user_status_manager_desc';
    public const USER_STATUS_MANAGER_NAME = 'user_status_manager_name';
    public const USER_STATUS_TRANSCRIBER_DESC = 'user_status_transcriber_desc';
    public const USER_STATUS_TRANSCRIBER_NAME = 'user_status_transcriber_name';
    public const USER_STATUS_VALIDATOR_DESC = 'user_status_validator_desc';
    public const USER_STATUS_VALIDATOR_NAME = 'user_status_validator_name';

    public const LIST = [
      self::PROJECT_STATUS_FINISHED_DESC,
      self::PROJECT_STATUS_FINISHED_NAME,
      self::PROJECT_STATUS_IN_PROGRESS_DESC,
      self::PROJECT_STATUS_IN_PROGRESS_NAME,
      self::PROJECT_STATUS_NEW_DESC,
      self::PROJECT_STATUS_NEW_NAME,
      self::TRANSCRIPTION_STATUS_IN_PROGRESS,
      self::TRANSCRIPTION_STATUS_IN_REREAD,
      self::TRANSCRIPTION_STATUS_NONE,
      self::TRANSCRIPTION_STATUS_VALIDATED,
      self::USER_STATUS_MANAGER_DESC,
      self::USER_STATUS_MANAGER_NAME,
      self::USER_STATUS_TRANSCRIBER_DESC,
      self::USER_STATUS_TRANSCRIBER_NAME,
      self::USER_STATUS_VALIDATOR_DESC,
      self::USER_STATUS_VALIDATOR_NAME
    ];
}
