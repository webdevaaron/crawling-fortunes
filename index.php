<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/connect.php';

use Orhanerday\OpenAi\OpenAi;
use Goutte\Client;

header('Content-Type: application/json');

$open_ai_key = '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';
$open_ai = new OpenAi($open_ai_key);

$client = new Client();

$zodiac_signs = [
    'Rat' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EC%A5%90%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Ox' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EC%86%8C%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Tiger' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%ED%98%B8%EB%9E%91%EC%9D%B4%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Rabbit' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%ED%86%A0%EB%81%BC%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Dragon' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EC%9A%A9%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Snake' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EB%B1%80%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Horse' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EB%A7%90%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Sheep' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EC%96%91%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Monkey' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EC%9B%90%EC%88%AD%EC%9D%B4%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Rooster' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EB%8B%AD%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Dog' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EA%B0%9C%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
    'Pig' => 'https://search.naver.com/search.naver?where=nexearch&sm=tab_etc&qvt=0&query=%EB%8F%BC%EC%A7%80%EB%9D%A0%20%EC%9A%B4%EC%84%B8',
];

$fortunes = [];

foreach ($zodiac_signs as $zodiac_sign => $url) {
    $crawler = $client->request('GET', $url);

    $result_panel = $crawler->filter('._resultPanel')->eq(0);
    if ($result_panel->count() > 0) {
        $fortune_title = $result_panel->filter('._label')->text();
        $fortune_time = $result_panel->filter('._cs_fortune_time')->text();
        $fortune_text = $result_panel->filter('._cs_fortune_text')->text();
        $fortune_lists_year = $result_panel->filter('._cs_fortune_list dt em')->each(function ($node) {
            return $node->text();
        });
        $fortune_lists = $result_panel->filter('._cs_fortune_list dd')->each(function ($node) {
            return $node->text();
        });

        $fortune_list_text = [];
        for ($i = 0; $i < count($fortune_lists); $i++) {
            $fortune_list_text[] = "{$fortune_lists_year[$i]} - {$fortune_lists[$i]}";
        }

        $fortunes[] = [
            'title' => $fortune_title,
            'zodiac_sign' => $zodiac_sign,
            'date' => $fortune_time,
            'short_description' => $fortune_text,
            'year_of_births_and_advices' => $fortune_list_text,
        ];
    } else {
        $fortunes[] = [
            'error' => "No fortune information found for $zodiac_sign",
        ];
    }

    usleep(1000000);
}

$data2 = $fortunes;

if(count($data2) == 12){
    foreach ($data2 as $data) {
        $dataFormatted = "title: " . $data['title'] . "\n";
        $dataFormatted .= "zodiac sign: " . $data['zodiac_sign'] . "\n";
        $dataFormatted .= "date: " . $data['date'] . "\n";
        $dataFormatted .= "short description/advice: " . $data['short_description'] . "\n";
        $dataFormatted .= "year of births and advices: \n";
    
        foreach ($data['year_of_births_and_advices'] as $index => $advice) {
            $dataFormatted .= $advice . "\n";
        }
    
        $dataFormatted = rtrim($dataFormatted, "\n");
    
        $user_message = "
        $dataFormatted
    
        => PARAPHRASE ALL OF IT!!
        => heres the format: paraphrase it also make it valuable.
        => PLEASE FOLLOW THE NOTES
        => DON'T FORGET THE BREAK TAGS
    
        <div style='margin-bottom: 50px'>
            <p><strong>Today's betting fortune for the year of the {zodiac sign} {date => mm/dd/yyyy}</strong></p>
            <p>&nbsp;</p>
            <p><strong>Common Fortune:/strong></p>
            <p>{note: 50-80 words and relate to short description/advice}</p>
            <p>&nbsp;</p>
            <p><strong>Betting Advice:</strong></p>
            <ul>{note: 5 list make it good and use li and onl}</ul>
            <p>&nbsp;</p>
            <p><strong>Fortune and Advice by Birth Year:</strong></p>
            <p><strong>Born in {year of birth}</strong></p>
            <ul>
                <li>Fortune-telling point: {advice}</li>
                <li>Betting Advice: {advice}</li>
            </ul>
            {
                NOTED: THEY HAVE 5 YEAR OF BIRTH, PLEASE LIST IT ALL
        
                example:
        
                <p><strong>Born in {year of birth}<strong><p>
                <ul>
                    <li>Fortune-telling point: {advice}</li>
                    <li>Betting Advice: {advice}</li>
                </ul>
        
                note:PLEASE COPY LIKE THAT
            }
            <p>&nbsp;</p>
            <p><strong>Conclusion:</strong></p>
            <p>{note: make 50-60 words and make it good ender}</p>
        </div>
        ";
    
        $chat = $open_ai->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    "role" => "system",
                    "content" => "You are a helpful assistant."
                ],
                [
                    "role" => "user",
                    "content" => $user_message
                ],
            ],
            'temperature' => 1.0,
            'max_tokens' => 4000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);
    
        $d = json_decode($chat);

        $name = $d->choices[0]->message->content;

        $stmt = $conn->prepare("INSERT INTO users (name) VALUES (:name)");

        $stmt->bindParam(':name', $name);

        if ($stmt->execute()) {
            echo $data['zodiac_sign'] ."-> New record created successfully <br>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

    }
}

$conn = null;
$data = ['message' => 'Hello, this is a message from the API.'];
echo json_encode($data);