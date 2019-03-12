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
    public const TRANSCRIPTION_LOG_VALIDATED = 'transcription_log_validated';
    public const TRANSCRIPTION_LOG_UNVALIDATED = 'transcription_log_unvalidated';
    public const TRANSCRIPTION_LOG_LOCKED = 'transcription_log_locked';
    public const TRANSCRIPTION_LOG_UNLOCKED = 'transcription_log_unlocked';

    // ProjectUserStatuses
    public const USER_STATUS_MANAGER_DESC = 'user_status_manager_desc';
    public const USER_STATUS_MANAGER_NAME = 'user_status_manager_name';
    public const USER_STATUS_TRANSCRIBER_DESC = 'user_status_transcriber_desc';
    public const USER_STATUS_TRANSCRIBER_NAME = 'user_status_transcriber_name';
    public const USER_STATUS_VALIDATOR_DESC = 'user_status_validator_desc';
    public const USER_STATUS_VALIDATOR_NAME = 'user_status_validator_name';

    // project actions
    public const ACTION_VIEW_TRANSCRIPTIONS = 'viewTranscriptions';
    public const ACTION_MANAGE_MEDIA = 'manageMedia';
    public const ACTION_MANAGE_USER = 'manageUser';
    public const ACTION_EDIT_PROJECT = 'editProject';
    public const ACTION_TRANSCRIBE = 'transcribe';
    public const ACTION_REGISTER = 'register';
    public const ACTION_VALIDATE_TRANSCRIPTION = 'validateTranscription';
    public const ACTION_VIEW_LOGS = 'viewLogs';
    public const ACTION_DELETE_COMMENT = 'deleteComment';
    public const ACTION_ARCHIVE = 'archive';

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
      self::TRANSCRIPTION_LOG_VALIDATED,
      self::TRANSCRIPTION_LOG_LOCKED,
      self::TRANSCRIPTION_LOG_UNLOCKED
    ];
}
