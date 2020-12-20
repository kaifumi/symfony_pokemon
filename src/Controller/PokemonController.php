<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PokemonController extends AbstractController
{
    /**
     * @Route("/pokemon", name="pokemon")
     */
    public function index(): Response
    {
        $fp           = fopen("../with_poke_lists.json", 'r');
        $json         = fgets($fp);
        $with_poke_lists = json_decode($json, true);
        fclose($fp);
        $poke_ranks = array_keys($with_poke_lists);

        return $this->render('pokemon/index.html.twig', [
            'poke_ranks' => $poke_ranks,
            'with_poke_lists' => $with_poke_lists,
        ]);
    }

    /**
     * @Route("/pokemon/recommend", name="pokemon_recommend")
     */
    public function pokemonRecommend(): Response
    {
        $fp           = fopen("../with_poke_lists.json", 'r');
        $json         = fgets($fp);
        $with_poke_lists = json_decode($json, true);
        fclose($fp);

        $poke_ranks = array_keys($with_poke_lists);
        // ランキングTOP30からランダム1匹選出
        $poke1 = $poke_ranks[rand(0, 29)];

        //0から9までの数値から抽選を行う
        $nums = range(0, 9);
        shuffle($nums);
        $nums = array_slice($nums, 0, 5);

        $pokemons = [];
        foreach ($nums as $num) {
            $pokemons[] = $with_poke_lists[$poke1][$num];
        }

        return $this->render('pokemon/recommend.html.twig', [
            'poke1' => $poke1,
            'pokemons' => $pokemons,
        ]);
    }
}
