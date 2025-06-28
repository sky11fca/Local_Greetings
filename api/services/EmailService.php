<?php
class EmailService{
    public function sendEmail($recipients, $subject, $htmlContent){
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: no-reply@' . $_SERVER['HTTP_HOST'],
            'Reply-To: no-reply@' . $_SERVER['HTTP_HOST'],
            'X-Mailer: PHP/' . phpversion()
        ];

        $results = [];
        $overallSuccess = true;

        foreach($recipients as $recipient){
            $result = mail($recipient, $subject, $htmlContent, implode("\r\n", $headers));

            if(!$result){
                $error = error_get_last();
                $results[]=[
                    'recipient' => $recipient,
                    'success' => false,
                    'error' => $error['message'] ?? 'Unknown error'
                ];
                $overallSuccess = false;
            }
            else
            {
                $results[]=[
                    'recipient' => $recipient,
                    'success' => true
                ];
            }
        }

        return [
            'overall_success' => $overallSuccess,
            'details' => $results
        ];
    }
    public function generateEmailContent($event, $rssHtml)
    {
        return "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .event-header { color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px; }
                    .event-details { margin: 15px 0; }
                    .rss-preview { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <h1 class='event-header'>New Event Created: {$event['title']}</h1>
                
                <div class='event-details'>
                    <p><strong>Description:</strong> {$event['description']}</p>
                    <p><strong>Location:</strong> {$event['field_name']} - {$event['field_address']}</p>
                    <p><strong>Time:</strong> " . date('Y-m-d H:i', strtotime($event['start_time'])) . " to " .
            date('Y-m-d H:i', strtotime($event['end_time'])) . "</p>
                    <p><strong>Sport Type:</strong> {$event['sport_type']}</p>
                </div>
                
                <div class='rss-preview'>
                    <h2>Event Preview</h2>
                    {$rssHtml}
                </div>
                
                <p style='margin-top:20px; font-size:0.9em; color:#777;'>
                    This email was generated automatically. The RSS feed is available at: 
                </p>
            </body>
            </html>
        ";
    }
}