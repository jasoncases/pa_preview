<?php

namespace Exception;

class PayrollShiftOverwriteExistingSummary extends Exception
{
    protected $message = "Existing summary record found for provided shift id";
};
