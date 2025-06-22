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

            if(!$this->rssModel->saveRSSFile($eventId, $rssContent)){
                throw new Exception('Error saving RSS file');
            }

//            $recipients = $this->rssModel->getRecipients();
//            if(empty($recipients)){
//                echo json_encode([
//                    'success' => true,
//                    'message' => 'No recipients found',
//                    'email_sent' => false
//                ]);
//            }

//            $subject = 'New Sport Event:  ' . $event['title'];
//
//            $htmlContent = $this->emailService->generateEmailContent($event);
//            $emailSent = $this->emailService->sendEmail(
//                $recipients,
//                $subject,
//                $htmlContent,
//                $rssContent,
//                $eventId
//            );


            echo json_encode([
                'success' => true,
                'message' => 'RSS feed generated successfully',
            ]);
        } catch(Exception $e){
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}