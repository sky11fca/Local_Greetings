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

    public function getEventParticipants($eventId)
    {
        $stmt = $this->db->prepare("SELECT u.username, u.email FROM Users u JOIN EventParticipants ep ON u.user_id = ep.user_id WHERE ep.event_id = ? AND ep.status = 'confirmed' ORDER BY ep.joined_at ASC");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecipients(){
        $stmt = $this->db->query("SELECT email FROM Users Where email LIKE '%@gmail.com'");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_column($results, 'email');
    }

    public function generateRSSFeed($event)
    {
        $rss = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"></rss>');
        $channel = $rss->addChild('channel');
        $channel->addChild('title', htmlspecialchars('Event Full: ' . $event['title']));
        $channel->addChild('description', htmlspecialchars($event['description']));

        $item = $channel->addChild('item');
        $item->addChild('title', htmlspecialchars('Event Full: ' . $event['title']));

        // Get participants
        $participants = $this->getEventParticipants($event['event_id']);
        $participantsList = "Participants:\n";
        foreach ($participants as $participant) {
            $participantsList .= "- " . htmlspecialchars($participant['username']) . "\n";
        }

        $description = "This event has reached maximum capacity!\n\n" .
            $event['description'] . "\n\n" .
            'Location: ' . $event['field_name'] . ' - ' . $event['field_address'] . "\n" .
            'Time: ' . date('Y-m-d H:i', strtotime($event['start_time'])) . ' to ' .
            date('Y-m-d H:i', strtotime($event['end_time'])) . "\n" .
            'Participants: ' . $event['current_participants'] . '/' . $event['max_participants'] . "\n\n" .
            $participantsList;

        $item->addChild('description', htmlspecialchars($description));
        $item->addChild('guid', $event['event_id']);
        $item->addChild('pubDate', date(DATE_RSS));
        return $rss->asXML();
    }

    public function rssToHtml($rssContent)
    {
        $rss = simplexml_load_string($rssContent);
        if(!$rss){
            return false;
        }

        $html = '<div class="rss-event">';
        foreach ($rss->channel->item as $item) {
            $html .= '<h2>' . htmlspecialchars($item->title) . '</h2>';
            $html .= '<div class="event-details">';
            $html .= nl2br(htmlspecialchars($item->description));
            $html .= '</div>';
            $html .= '<div class="event-meta">';
            $html .= '<p>Published: ' . date('Y-m-d H:i', strtotime($item->pubDate)) . '</p>';
            $html .= '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    public function getEventUrl($eventId)
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/local_greeter/api/events/' . $eventId;
    }


}
