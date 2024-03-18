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
            $sql = "INSERT INTO g5_write_fortune (wr_id, wr_num, wr_reply, wr_parent, wr_is_comment, wr_comment, wr_comment_reply, ca_name, wr_option, wr_subject, wr_content, wr_seo_title, wr_link1, wr_link2, wr_link1_hit, wr_link2_hit, wr_hit, wr_good, wr_nogood, mb_id, wr_password, wr_name, wr_email, wr_homepage, wr_datetime, wr_file, wr_last, wr_ip, wr_facebook_user, wr_twitter_user, wr_1, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_10, as_type, as_img, as_extend, as_down, as_view, as_star_score, as_star_cnt, as_choice, as_choice_cnt, as_tag, as_thumb)
            VALUES (:wr_id, :wr_num, :wr_reply, :wr_parent, :wr_is_comment, :wr_comment, :wr_comment_reply, :ca_name, :wr_option, :wr_subject, :wr_content, :wr_seo_title, :wr_link1, :wr_link2, :wr_link1_hit, :wr_link2_hit, :wr_hit, :wr_good, :wr_nogood, :mb_id, :wr_password, :wr_name, :wr_email, :wr_homepage, :wr_datetime, :wr_file, :wr_last, :wr_ip, :wr_facebook_user, :wr_twitter_user, :wr_1, :wr_2, :wr_3, :wr_4, :wr_5, :wr_6, :wr_7, :wr_8, :wr_9, :wr_10, :as_type, :as_img, :as_extend, :as_down, :as_view, :as_star_score, :as_star_cnt, :as_choice, :as_choice_cnt, :as_tag, :as_thumb)";
            $stmt = $conn->prepare($sql);

            $stmt->bindParam(':wr_id', $wr_id);
            $stmt->bindParam(':wr_num', $wr_num);
            $stmt->bindParam(':wr_reply', $wr_reply);
            $stmt->bindParam(':wr_parent', $wr_parent);
            $stmt->bindParam(':wr_is_comment', $wr_is_comment);
            $stmt->bindParam(':wr_comment', $wr_comment);
            $stmt->bindParam(':wr_comment_reply', $wr_comment_reply);
            $stmt->bindParam(':ca_name', $ca_name);
            $stmt->bindParam(':wr_option', $wr_option);
            $stmt->bindParam(':wr_subject', $wr_subject);
            $stmt->bindParam(':wr_content', $wr_content);
            $stmt->bindParam(':wr_seo_title', $wr_seo_title);
            $stmt->bindParam(':wr_link1', $wr_link1);
            $stmt->bindParam(':wr_link2', $wr_link2);
            $stmt->bindParam(':wr_link1_hit', $wr_link1_hit);
            $stmt->bindParam(':wr_link2_hit', $wr_link2_hit);
            $stmt->bindParam(':wr_hit', $wr_hit);
            $stmt->bindParam(':wr_good', $wr_good);
            $stmt->bindParam(':wr_nogood', $wr_nogood);
            $stmt->bindParam(':mb_id', $mb_id);
            $stmt->bindParam(':wr_password', $wr_password);
            $stmt->bindParam(':wr_name', $wr_name);
            $stmt->bindParam(':wr_email', $wr_email);
            $stmt->bindParam(':wr_homepage', $wr_homepage);
            $stmt->bindParam(':wr_datetime', $wr_datetime);
            $stmt->bindParam(':wr_file', $wr_file);
            $stmt->bindParam(':wr_last', $wr_last);
            $stmt->bindParam(':wr_ip', $wr_ip);
            $stmt->bindParam(':wr_facebook_user', $wr_facebook_user);
            $stmt->bindParam(':wr_twitter_user', $wr_twitter_user);
            $stmt->bindParam(':wr_1', $wr_1);
            $stmt->bindParam(':wr_2', $wr_2);
            $stmt->bindParam(':wr_3', $wr_3);
            $stmt->bindParam(':wr_4', $wr_4);
            $stmt->bindParam(':wr_5', $wr_5);
            $stmt->bindParam(':wr_6', $wr_6);
            $stmt->bindParam(':wr_7', $wr_7);
            $stmt->bindParam(':wr_8', $wr_8);
            $stmt->bindParam(':wr_9', $wr_9);
            $stmt->bindParam(':wr_10', $wr_10);
            $stmt->bindParam(':as_type', $as_type);
            $stmt->bindParam(':as_img', $as_img);
            $stmt->bindParam(':as_extend', $as_extend);
            $stmt->bindParam(':as_down', $as_down);
            $stmt->bindParam(':as_view', $as_view);
            $stmt->bindParam(':as_star_score', $as_star_score);
            $stmt->bindParam(':as_star_cnt', $as_star_cnt);
            $stmt->bindParam(':as_choice', $as_choice);
            $stmt->bindParam(':as_choice_cnt', $as_choice_cnt);
            $stmt->bindParam(':as_tag', $as_tag);
            $stmt->bindParam(':as_thumb', $as_thumb);
            
            // Set parameter values
            $wr_id = 1;
            $wr_num = -1;
            $wr_reply = " ";
            $wr_parent = 1;
            $wr_is_comment = 0;
            $wr_comment = 0;
            $wr_comment_reply = " ";
            $ca_name = " ";
            $wr_option = "html1";
            $wr_subject = "test";
            $wr_content = $d->choices[0]->message->content;
            $wr_seo_title = "test";
            $wr_link1 = " ";
            $wr_link2 = " ";
            $wr_link1_hit = 0;
            $wr_link2_hit = 0;
            $wr_hit = 1;
            $wr_good = 0;
            $wr_nogood = 0;
            $mb_id = "admin";
            $wr_password = " ";
            $wr_name = "오벳관리자";
            $wr_email = "obet24@proton.me";
            $wr_homepage = " ";
            $wr_datetime = "2024-03-09 11:04:10";
            $wr_file = 1;
            $wr_last = "2024-03-09 11:04:10";
            $wr_ip = "210.223.12.111";
            $wr_facebook_user = " ";
            $wr_twitter_user = " ";
            $wr_1 = " ";
            $wr_2 = " ";
            $wr_3 = " ";
            $wr_4 = " ";
            $wr_5 = " ";
            $wr_6 = " ";
            $wr_7 = " ";
            $wr_8 = " ";
            $wr_9 = " ";
            $wr_10 = " ";
            $as_type = 0;
            $as_img = 0;
            $as_extend = 0;
            $as_down = 0;
            $as_view = 0;
            $as_star_score = 0;
            $as_star_cnt = 0;
            $as_choice = 0;
            $as_choice_cnt = 0;
            $as_tag = " ";
            $as_thumb = ".//data/file/fortune/03a043d5693f411f4b0f1b171e5d84dc_oNUf7vYE_40759a766aef32185b6531eeb8e2994651071d53.png";

            try {
                $stmt->execute();
                echo "New record inserted successfully";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            
            echo $data['zodiac_sign'] ."-> New record created successfully <br>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }

    }
}

$conn = null;
$data = ['message' => 'Hello, this is a message from the API.'];
echo json_encode($data);