<?php

namespace App\Enums;

enum PipelineStatus: string
{
    case Sourced = 'sourced';
    case Screening = 'screening';
    case Interview = 'interview';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';
}
