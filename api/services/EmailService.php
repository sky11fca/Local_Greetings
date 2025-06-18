<?php
class EmailService{
    public function sendEmail($recipients, $subject, $htmlContent, $rssContent, $eventId){
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: no-reply@' . $_SERVER['HTTP_HOST'],
            'Reply-To: no-reply@' . $_SERVER['HTTP_HOST'],
            'X-Mailer: PHP/' . phpversion()
        ];

        $boundary = md5(time());

        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $htmlContent . "\r\n\r\n";

        //ATTACH RSS

        $body .= "--$boundary\r\n";
        $body .= "Content-Type: application/rss+xml; name=\"event_{$eventId}.rss\" charset=utf-8\r\n\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= "Content-Disposition: attachment\r\n\r\n";
        $body .= chunk_split(base64_encode($rssContent)) . "\r\n\r\n";

        $success = true;

        foreach($recipients as $recipient){
            if(!mail($recipient, $subject, $body, implode("\r\n", $headers))){
                $success = false;
                error_log("Error sending email to {$recipient}");
            }
        }

        return $success;
    }

    //TODO: Do something about the anchor thing
    public function generateEmailContent($event) {
        return "
            <h1>New Event Created: {$event['title']}</h1>
            <p>{$event['description']}</p>
            <p><strong>Location:</strong> {$event['field_name']} - {$event['field_address']}</p>
            <p><strong>Time:</strong> " . date('Y-m-d H:i', strtotime($event['start_time'])) . " to " . date('Y-m-d H:i', strtotime($event['end_time'])) . "</p>
            <p><strong>Sport Type:</strong> {$event['sport_type']}</p>
            <p>The RSS feed for this event is attached.</p>
        ";
    }
}