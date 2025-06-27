<?php
require_once __DIR__ . '/../models/RSSFeedModel.php';
require_once __DIR__ . '/../services/EmailService.php';

class RSSFeedController{
    private $rssModel;
    private $emailService;
    public function __construct($db)
    {
        $this->rssModel = new RSSFeedModel($db);
        $this->emailService = new EmailService();
    }

    public function generateAndNotify(){
        try{
            $data = json_decode(file_get_contents('php://input'), true);
            $eventId = $data['event_id'];

            $event = $this->rssModel->getEventDetails($eventId);
            if(!$event){
                throw new Exception('Event not found');
            }

            $rssContent = $this->rssModel->generateRSSFeed($event);
            $rssHtml = $this->rssModel->rssToHtml($rssContent);


//            $recipients = $this->rssModel->getRecipients();
//            if(empty($recipients)){
//                echo json_encode([
//                    'success' => true,
//                    'message' => 'No recipients found',
//                    'email_sent' => false
//                ]);
//            }

            $subject = 'New Sport Event:  ' . $event['title'];

//            $htmlContent = $this->emailService->generateEmailContent($event, $rssHtml);
//            $emailSent = $this->emailService->sendEmail(
//                $recipients,
//                $subject,
//                $htmlContent,
//            );
//
//            if(!$emailSent['overall_success']){
//                http_response_code(400);
//                echo json_encode([
//                    'success' => false,
//                    'message' => 'Error sending email',
//                    'errors' => array_filter($emailSent['details'], function($item){
//                        return !$item['success'];
//                    })
//                ]);
//                return;
//            }


            echo json_encode([
                'success' => true,
                'message' => 'RSS feed generated successfully',
                'rss_feed_url' => $this->rssModel->getEventUrl($eventId),
            ]);
        } catch(Exception $e){
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function getEventRss(){

        header('Content-Type: application/xml; charset=utf-8');
        header('Cache-Control: max-age=3600');

        $eventId = $_GET['event_id'];

        $event = $this->rssModel->getEventDetails($eventId);
        if(!$event){
            http_response_code(404);
            echo 'Event not found';
            exit;
        }
        $rssContent = $this->rssModel->generateRSSFeed($event);
        echo $rssContent;
    }
}