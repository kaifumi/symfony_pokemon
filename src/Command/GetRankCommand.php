<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GetRankCommand extends Command
{
    protected static $defaultName = 'getRank';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 使用するフォルダを作成
        if (!is_dir('./JSON')){
            mkdir('JSON');
            mkdir('JSON/POKEMON_INFO');
            mkdir('JSON/RANKING');
            mkdir('JSON/SEASON');
        }

        // 対戦環境を取得
        $cmd = "curl https://api.battle.pokemon-home.com/cbd/competition/rankmatch/list \
                -H 'accept: application/json, text/javascript, */*; q=0.01' \
                -H 'countrycode: 304' \
                -H 'authorization: Bearer' \
                -H 'langcode: 1' \
                -H 'user-agent: Mozilla/5.0 (Linux; Android 8.0; Pixel 2 Build/OPD3.170816.012) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Mobile Safari/537.36' \
                -H 'content-type: application/json' \
                -d '{\"soft\":\"Sw\"}' \
                -o JSON/SEASON/season.json";
        echo exec($cmd);

        // シーズン・タームのID情報
        $fp         = fopen('./JSON/SEASON/season.json', 'r');
        $json       = fgets($fp);
        // trueにして連想配列にする
        $battle_env = json_decode($json, true);
        fclose($fp);

        # タームごとに必要な情報だけを配列にまとめる
        $terms = [];
        foreach ($battle_env['list'] as $data) {
        $id_num = array_keys($data)[0];
        foreach ($data as $id) {

            $season = $id['season'];
            $rule   = $id['rule'];
            $rst    = $id['rst'];
            $ts1    = $id['ts1'];
            $ts2    = $id['ts2'];

            $terms[] = array('id' => $id_num, 'season' => $season, 'rule' => $rule, 'rst' => $rst, 'ts1' => $ts1, 'ts2' => $ts2);
        }
        }

        // 対戦環境ポケモン情報を取得
        foreach ($terms as $term) {
        if ($term['rule'] == 0) {

            $id  = $term['id'];
            $rst = $term['rst'];
            $ts2 = $term['ts2'];
            $pokemons_file = $term['id']."-pokemons.json";
            // ポケモン情報保存用のフォルダ作成
            if (!is_dir("./JSON/POKEMON_INFO/season-$id")){
            mkdir("./JSON/POKEMON_INFO/season-$id");
            }
            // 使用率ランキングを取得
            $cmd = "curl -XGET https://resource.pokemon-home.com/battledata/ranking/$id/$rst/$ts2/pokemon -H 'user-agent: Mozilla/5.0 (Linux; Android 8.0; Pixel 2 Build/OPD3.170816.012) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Mobile Safari/537.36'  -H 'accept: application/json' -o JSON/RANKING/$pokemons_file";
            echo exec($cmd);

            for ($i=0;$i<5;$i++) {
            $j = $i + 1;
            $pokeinfo_file = $id."-pokeinfo-".$j.".json";

            // ポケモンの同時選出、採用技構成や持ち物などを取得
            $cmd = "curl -XGET https://resource.pokemon-home.com/battledata/ranking/$id/$rst/$ts2/pdetail-$j -H 'user-agent: Mozilla/5.0 (Linux; Android 8.0; Pixel 2 Build/OPD3.170816.012) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Mobile Safari/537.36'  -H 'accept: application/json' -o JSON/POKEMON_INFO/season-$id/$pokeinfo_file";
            echo exec($cmd);
            }
        }
        // 他のtermも取得する場合はbreakをコメントしてください
        break;
        }

        // 最新環境ランキング取得
        $recent_id       = $terms[0]['id'];
        $fp              = fopen("./JSON/RANKING/$recent_id-pokemons.json", 'r');
        $json            = fgets($fp);
        $pokemon_ranking = json_decode($json, true);
        fclose($fp);

        // 各ポケモンの情報を用意
        $pokemons_info = [];
        for ($i=0;$i<5;$i++) {
        $j            = $i + 1;
        $fp           = fopen("./JSON/POKEMON_INFO/season-$recent_id/$recent_id-pokeinfo-$j.json", 'r');
        $json         = fgets($fp);
        $pokemon_data = json_decode($json, true);
        fclose($fp);

        foreach (array_keys($pokemon_data) as $index) {
            $pokemons_info[$index] = $pokemon_data[$index];
        }
        }

        // トップ30匹を抽出
        $top_pokemons = array_slice($pokemon_ranking, 0, 30);
        $with_poke_lists = [];
        foreach ($top_pokemons as $pokemon) {

        // よく一緒に選出されるポケモンリスト
        $with_poke_list = $pokemons_info[$pokemon['id']][$pokemon['form']]["temoti"]["pokemon"];
        if ($pokemon['form'] != 0) {
            $pokemon['id'] = $pokemon['id']."_".$pokemon['form'];
        }
        $with_poke_lists[$pokemon['id']] = $with_poke_list;
        }

        $json = json_encode($with_poke_lists);
        file_put_contents("with_poke_lists.json", $json);
        return true;
    }
}
