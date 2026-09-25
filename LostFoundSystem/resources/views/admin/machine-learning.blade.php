@extends('layouts.app')
@section('title', 'Machine Learning Matching — Admin Panel')

@section('content')
<div class="admin-shell">
    @include('components.admin-top-nav', ['userData' => $userData])

    <main class="admin-main">
        <div class="admin-topbar">
            <div>
                <h1 class="admin-page-title">Machine Learning Matching</h1>
                <p class="admin-page-sub">How the website compares lost and found item descriptions.</p>
            </div>
            <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-check-circle me-1"></i> Engine configured</span>
        </div>

        <div class="alert alert-primary border-0 rounded-4 shadow-sm">
            <h5 class="fw-bold"><i class="fas fa-info-circle me-2"></i>This is an on-demand NLP model</h5>
            <p class="mb-0">The system does not use a pre-trained neural network. For each new report, it fits a TF-IDF vocabulary on the current opposite-side records, transforms the new item and candidates into vectors, and ranks them using cosine similarity. This keeps matches current as records change.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3"><div class="admin-kpi-card kpi-blue"><div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div><div class="kpi-body"><div class="kpi-value">{{ $lostCount }}</div><div class="kpi-label">Lost records</div></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="admin-kpi-card kpi-green"><div class="kpi-icon"><i class="fas fa-hand-holding-heart"></i></div><div class="kpi-body"><div class="kpi-value">{{ $foundCount }}</div><div class="kpi-label">Found records</div></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="admin-kpi-card kpi-amber"><div class="kpi-icon"><i class="fas fa-link"></i></div><div class="kpi-body"><div class="kpi-value">{{ $matchCount }}</div><div class="kpi-label">Generated matches</div></div></div></div>
            <div class="col-sm-6 col-xl-3"><div class="admin-kpi-card kpi-red"><div class="kpi-icon"><i class="fas fa-percent"></i></div><div class="kpi-body"><div class="kpi-value">{{ $averageSimilarity }}%</div><div class="kpi-label">Average similarity</div></div></div></div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="admin-card h-100">
                    <div class="admin-card-header"><h5 class="admin-card-title"><i class="fas fa-project-diagram text-primary me-2"></i>Matching pipeline</h5></div>
                    <div class="p-4">
                        @foreach([
                            ['1', 'Build text', 'Combines item name, description, category, color, brand, and location.'],
                            ['2', 'Clean text', 'Lowercases, removes non-letter characters, tokenizes, removes stop words, and lemmatizes where NLTK data is available.'],
                            ['3', 'Fit TF-IDF', 'Builds a term-frequency/inverse-document-frequency vocabulary from the candidate records plus the new item.'],
                            ['4', 'Compare vectors', 'Calculates cosine similarity between the new item vector and every candidate vector.'],
                            ['5', 'Rank results', 'Returns the five highest non-zero scores as percentage matches and stores them in the application.'],
                        ] as [$number, $title, $description])
                            <div class="d-flex gap-3 mb-4">
                                <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;">{{ $number }}</span>
                                <div><h6 class="fw-bold mb-1">{{ $title }}</h6><p class="text-secondary small mb-0">{{ $description }}</p></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="admin-card mb-4">
                    <div class="admin-card-header"><h5 class="admin-card-title"><i class="fas fa-server text-success me-2"></i>Engine connection</h5></div>
                    <div class="p-4">
                        <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Endpoint</span><code>{{ $nlpUrl }}</code></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Algorithm</span><strong>TF-IDF + cosine</strong></div>
                        <div class="d-flex justify-content-between"><span class="text-secondary">Fallback</span><strong>PHP text similarity</strong></div>
                    </div>
                </div>
                <div class="admin-card">
                    <div class="admin-card-header"><h5 class="admin-card-title"><i class="fas fa-shield-alt text-warning me-2"></i>Reliability</h5></div>
                    <div class="p-4 text-secondary small">
                        If the optional Flask service is unavailable, Laravel logs the failure and uses its built-in similarity fallback. New reports still receive matches without silently failing.
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection
