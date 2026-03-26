<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\EpisodeLink;
use App\Models\Season;
use App\Models\Serie;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    public function index(Request $request)
    {
        // Inicia query
        $query = Serie::query();

        // Pesquisa por título ou ano
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('first_air_year', 'like', "%{$search}%");
        }

        // Paginação: 10 filmes por página, ordenados por ID desc
        $series = $query->orderBy('id', 'desc')->paginate(10);

        // Mantém o termo de pesquisa na paginação
        if ($request->has('search')) {
            $series->appends(['search' => $request->search]);
        }

        // Retorna view com os filmes
        return view('admin.series.index', compact('series'));
    }

    public function seasons(Serie $serie)
    {
        $seasons = $serie->seasons()
            ->orderBy('season_number')
            ->paginate(10);

        return view('admin.series.seasons', compact('serie', 'seasons'));
    }

    public function updateSeason(Request $request, Season $season)
    {
        $request->validate([
            'status' => 'required'
        ]);

        $season->update([
            'status' => $request->status
        ]);

        return back()->with('success', 'Temporada atualizada!');
    }

    public function episodes(Season $season)
    {
        $episodes = $season->episodes()
            ->orderBy('episode_number')
            ->paginate(10);

        return view('admin.series.episodes', compact('season', 'episodes'));
    }

    public function updateEpisode(Request $request, Episode $episode)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required'
        ]);

        $episode->update([
            'name' => $request->name,
            'status' => $request->status
        ]);

        return back()->with('success', 'Episódio atualizado com sucesso!');
    }

    public function deleteEpisode(Episode $episode)
    {
        $season = $episode->season;

        $episode->delete();

        return redirect()
            ->route('admin.series.episodes', $season->id)
            ->with('success', 'Episódio removido com sucesso!');
    }

    public function episodeLinks(Episode $episode)
    {
        $links = $episode->links()
            ->orderBy('order')
            ->paginate(10);

        return view('admin.series.links.index', compact('episode', 'links'));
    }

    public function createEpisodeLink(Episode $episode)
    {
        return view('admin.series.links.create', compact('episode'));
    }

    private function timeToSeconds($time)
    {
        if (!$time) {
            return null;
        }

        if (!str_contains($time, ':')) {
            return null;
        }

        [$minutes, $seconds] = explode(':', $time);

        return ((int) $minutes * 60) + (int) $seconds;
    }

    public function storeEpisodeLink(Request $request, Episode $episode)
    {
        $data = $request->all();

        $data['skip_intro_start'] = $this->timeToSeconds($request->skip_intro_start);
        $data['skip_intro_end'] = $this->timeToSeconds($request->skip_intro_end);

        $data['skip_ending_start'] = $this->timeToSeconds($request->skip_ending_start);
        $data['skip_ending_end'] = $this->timeToSeconds($request->skip_ending_end);

        $episode->links()->create($data);

        return redirect()
            ->route('admin.series.episodes.links', $episode)
            ->with('success', 'Link criado!');
    }

    private function secondsToTime($seconds)
    {
        if ($seconds === null) {
            return null;
        }

        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function editEpisodeLink(EpisodeLink $link)
    {
        return view('admin.series.links.edit', compact('link'));
    }

    public function updateEpisodeLink(Request $request, EpisodeLink $link)
    {
        $request->validate([
            'name' => 'required',
            'url' => 'required',
            'type' => 'required'
        ]);

        $data = $request->all();

        $data['skip_intro_start'] = $this->timeToSeconds($request->skip_intro_start);
        $data['skip_intro_end'] = $this->timeToSeconds($request->skip_intro_end);

        $data['skip_ending_start'] = $this->timeToSeconds($request->skip_ending_start);
        $data['skip_ending_end'] = $this->timeToSeconds($request->skip_ending_end);

        $link->update($data);

        return redirect()
            ->route('admin.series.episodes.links', $link->episode_id)
            ->with('success', 'Link atualizado!');
    }

    public function deleteEpisodeLink(EpisodeLink $link)
    {
        $episode = $link->episode;

        $link->delete();

        return redirect()
            ->route('admin.series.episodes.links', $episode)
            ->with('success', 'Link removido!');
    }

    public function destroy(Serie $serie)
    {
        $serie->delete();

        return redirect()->route('admin.series.index')
            ->with('success', 'Série deletado com sucesso!');
    }
}
