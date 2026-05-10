<?php

namespace App\Http\Controllers;

use App\Services\PracticeSessionService;
use Inertia\Inertia;
use Inertia\Response;

class PublicDisplayController extends Controller
{
    public function __construct(
        protected PracticeSessionService $practiceSessionService,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('TemuRawat/Display/Index', [
            'sessions' => $this->practiceSessionService->serializeSessionCollection(
                $this->practiceSessionService->getTodaySessions(),
            ),
        ]);
    }
}
