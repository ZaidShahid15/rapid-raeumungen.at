<?php

namespace App\Http\Controllers;

use App\Support\FormMailHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FormSubmissionController extends Controller
{
    public function submit(Request $request, ?string $path = null): RedirectResponse
    {
        $payload = FormMailHelper::extractPayload($request->except('_token'));
        $formFeedbackTarget = [
            'form_id' => $request->input('form_id'),
            'page_id' => $request->input('page_id'),
            'post_id' => $request->input('post_id'),
        ];

        if ($payload === []) {
            return back()
                ->with('form_status', 'empty')
                ->with('form_feedback_target', $formFeedbackTarget);
        }

        $pagePath = '/' . ltrim($path ?? '', '/');
        $pagePath = $pagePath === '/' ? '/' : rtrim($pagePath, '/');

        try {
            Mail::send(
                FormMailHelper::templateViewFor($payload),
                FormMailHelper::templateDataFor($payload),
                function ($message) use ($payload, $pagePath) {
                    $replyTo = FormMailHelper::replyTo($payload);
                    $fromAddress = Config::get('mail.from.address');
                    $fromName = Config::get('mail.from.name', 'ast Media');

                    $message
                        ->from($fromAddress, $fromName)
                        ->to(FormMailHelper::RECIPIENT_EMAILS)
                        ->subject(FormMailHelper::subjectFor($pagePath, $payload));

                    if ($replyTo !== null) {
                        $message->replyTo($replyTo);
                    }
                }
            );

            return back()
                ->with('form_status', 'success')
                ->with('form_feedback_target', $formFeedbackTarget);
        } catch (\Throwable $exception) {
            Log::error('Form submission mail failed.', [
                'page' => $pagePath,
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->with('form_status', 'error')
                ->with('form_feedback_target', $formFeedbackTarget);
        }
    }
}
