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

    // TranscriptionLogs
    public const TRANSCRIPTION_LOG_CREATED = 'transcription_log_created';
    public const TRANSCRIPTION_LOG_UPDATED = 'transcription_log_updated';
    public const TRANSCRIPTION_LOG_REREADED = 'transcription_log_rereaded';
    public const TRANSCRIPTION_LOG_WAITING_FOR_VALIDATION = 'transcription_log_waiting_for_validation';
    public const TRANSCRIPTION_LOG_VALIDATION_PENDING = 'transcription_log_validation_pending';
    public const TRANSCRIPTION_LOG_VALIDATED = 'transcription_log_validated';

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
      self::USER_STATUS_MANAGER_DESC,
      self::USER_STATUS_MANAGER_NAME,
      self::USER_STATUS_TRANSCRIBER_DESC,
      self::USER_STATUS_TRANSCRIBER_NAME,
      self::USER_STATUS_VALIDATOR_DESC,
      self::USER_STATUS_VALIDATOR_NAME,
      self::TRANSCRIPTION_LOG_CREATED,
      self::TRANSCRIPTION_LOG_UPDATED,
      self::TRANSCRIPTION_LOG_REREADED,
      self::TRANSCRIPTION_LOG_VALIDATED,
      self::TRANSCRIPTION_LOG_WAITING_FOR_VALIDATION,
      self::TRANSCRIPTION_LOG_VALIDATION_PENDING,
      self::TRANSCRIPTION_LOG_VALIDATED
    ];
}
