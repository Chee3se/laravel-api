<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\Lesson;
use DOMDocument;
use Illuminate\Console\Command;
use PhpParser\Node\Stmt\Foreach_;

class Update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //Wipes the whole database (not good)
        Lesson::query()->delete();
        Group::query()->delete();
        //Get a list of every specified element in url
        function getArray($link, $element) {
            $html = file_get_contents('http://pt.edu.lv/pt/stundas.php'.$link);
            $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
            $DOM = new DOMDocument();
            @$DOM->loadHTML($html);
            $links = $DOM->getElementsByTagName($element);
            return $links;
        }
        //Group getting
        $id = 1;
        $foundGroups = getArray('', 'a');
        foreach ($foundGroups as $link) {
            $href = $link->getAttribute('href');
            if (preg_match('/\?id=m&g=([^&]+)/', $href, $matches)) {
                $groupName = $matches[1];
                Group::create(['name' => $new_group]);
            }
            $id++;
        }
        //Lesson getting
        $id = 1;
        $groups = Group::all();
        foreach ($groups as $group) {
            $foundLessons = getArray('?id=m&g='.$group['name'], 'td');
            $day = 0;
            $lesson = ':/';
            $teacher = ':/';
            $started = false;
            $count = 0;
            foreach ($foundLessons as $foundLesson) {
                $text = $foundLesson->nodeValue;
                // Incrementing day
                if ($started == true) {

                    if (str_contains($text, 'Pārst.')) {

                        $day++;

                    } else if (!is_numeric($text) && !str_contains($text, "\n")) {
                        if ($count == 0) {

                            $lesson = $text;
                            $count = 1;

                        } else if ($count == 1) {

                            $teacher = $text;
                            $count = 2;

                        } else  {
                            if ($count == 2) {
                                if (empty($text)) {
                                    Lesson::create([
                                        'day' => $day,
                                        'group' => $group['name'],
                                        'lesson' => $lesson,
                                        'teacher' => $teacher,
                                    ]);
                                    $count = 0;
                                    $id++;
                                } else {

                                    $lesson = $text;
                                    $count = 3;

                                }
                            } else if ($count == 3) {

                                Lesson::create([
                                    'day' => $day,
                                    'group' => $group['name'],
                                    'lesson' => $lesson,
                                    'teacher' => $text,
                                ]);
                                $count = 0;
                                $id++;
                            }

                        }

                    }
                }
                // Getting valuable info
                if ($text == "Dienas ") {
                    $started = true;
                }
            }
        }
        //Solution: just update lol
        /*

        */
    }
}
