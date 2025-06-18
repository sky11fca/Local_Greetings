<?php
class RSSFeedModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getEventDetails($eventId)
    {
        $stmt = $this->db->prepare("SELECT e.*, sf.name as field_name, sf.address as field_address 
            FROM Events e 
            JOIN SportsFields sf ON e.field_id = sf.field_id 
            WHERE e.event_id = ?");

        $stmt->execute([$eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecipients(){
        $stmt = $this->db->query("SELECT email FROM Users Where email LIKE '%@gmail.com'");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($results, 'email');
    }

    public function generateRSSFeed($event){
        $rss = '<?xml version="1.0" encoding="UTF-8"?>';
        $rss .= '<rss version="2.0">';
        $rss .= '<channel>';
        $rss .= '<title>New Sports Event: ' . htmlspecialchars($event['title']) . '</title>';
       // $rss .= '<link>' . $this->getEventUrl($event['event_id']) . '</link>';
        $rss .= '<description>' . htmlspecialchars($event['description']) . '</description>';
        $rss .= '<item>';
        $rss .= '<title>' . htmlspecialchars($event['title']) . '</title>';
        $rss .= '<description>';
        $rss .= htmlspecialchars($event['description']) . '\\n\\n';
        $rss .= 'Location: ' . htmlspecialchars($event['field_name'] . ' - ' . $event['field_address']) . '\\n';
        $rss .= 'Time: ' . date('Y-m-d H:i', strtotime($event['start_time'])) . ' to ' . date('Y-m-d H:i', strtotime($event['end_time']));
        $rss .= '</description>';
       // $rss .= '<link>' . $this->getEventUrl($event['event_id']) . '</link>';
        $rss .= '<guid>' . $event['event_id'] . '</guid>';
        $rss .= '<pubDate>' . date(DATE_RSS) . '</pubDate>';
        $rss .= '</item>';
        $rss .= '</channel>';
        $rss .= '</rss>';

        return $rss;
    }

    public function saveRSSFile($eventId, $rssContent){
        $rssDir = __DIR__ . "/../../public/rss/";
        if(!file_exists($rssDir)){
            mkdir($rssDir, 0755, true);
        }

        $rssFileName = 'event_' . $eventId . '.rss';
        return file_put_contents($rssDir . $rssFileName, $rssContent);
    }


    //TODO solve with the event url and stuff
    private function getEventUrl($eventId){
        return 'http://localhost:8080/api/events/' . $eventId;
    }

}
