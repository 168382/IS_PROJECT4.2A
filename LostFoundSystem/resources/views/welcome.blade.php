@extends('layouts.app')
@section('title', 'Lost and Found Tracking System')

@section('content')
    <!-- ─── Hero ────────────────────────────────────────────── -->
    <section class="hero-section text-center">
        <div class="container position-relative" style="z-index:2;">
            <h1 class="hero-title mb-3 animate-in">Lost and Found<br>Tracking System</h1>
            <p class="hero-subtitle mb-4 animate-in animate-delay-1">
                Powered by Natural Language Processing to automatically match lost belongings with found items across the campus.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap animate-in animate-delay-2">
                <a href="{{ url('/report/lost') }}" class="hero-btn hero-btn-white">
                    <i class="fas fa-search me-2"></i> I Lost Something
                </a>
                <a href="{{ url('/report/found') }}" class="hero-btn hero-btn-outline">
                    <i class="fas fa-hand-holding-heart me-2"></i> I Found Something
                </a>
            </div>
        </div>
    </section>

    <!-- ─── Features ────────────────────────────────────────── -->
    <section class="py-5">
        <div class="container" style="margin-top:-2rem;">
            <div class="row g-4">
                <div class="col-md-4 animate-in animate-delay-1">
                    <div class="feature-card h-100">
                        <div class="feature-icon blue"><i class="fas fa-brain"></i></div>
                        <h5>AI-Powered Matching</h5>
                        <p>Our NLP engine uses TF-IDF vectorization and cosine similarity to intelligently match item descriptions and reunite you with your belongings faster.</p>
                    </div>
                </div>
                <div class="col-md-4 animate-in animate-delay-2">
                    <div class="feature-card h-100">
                        <div class="feature-icon green"><i class="fas fa-bell"></i></div>
                        <h5>Instant Notifications</h5>
                        <p>Receive real-time dashboard alerts the moment a potential match is found for your reported item. Never miss a recovery opportunity.</p>
                    </div>
                </div>
                <div class="col-md-4 animate-in animate-delay-3">
                    <div class="feature-card h-100">
                        <div class="feature-icon amber"><i class="fas fa-chart-line"></i></div>
                        <h5>Smart Analytics</h5>
                        <p>Administrators can view detailed recovery statistics, identify campus hot-spots for lost items, and generate comprehensive PDF & Excel reports.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── How It Works ────────────────────────────────────── -->
    <section class="pb-5">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="fw-bold">How It Works</h2>
                <p class="text-secondary">Three simple steps to find your lost belongings</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="feature-icon blue mx-auto mb-3"><i class="fas fa-file-alt"></i></div>
                        <h5 class="fw-bold">1. Report</h5>
                        <p class="text-secondary">Submit a detailed report of your lost or found item including name, description, color, brand, and location.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="feature-icon green mx-auto mb-3"><i class="fas fa-cogs"></i></div>
                        <h5 class="fw-bold">2. AI Matches</h5>
                        <p class="text-secondary">Our NLP engine automatically compares your report against the database and finds the top 5 most similar items.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4">
                        <div class="feature-icon amber mx-auto mb-3"><i class="fas fa-handshake"></i></div>
                        <h5 class="fw-bold">3. Retrieve</h5>
                        <p class="text-secondary">Review the matching results, submit a claim with proof of ownership, and collect your item once approved.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── CTA ─────────────────────────────────────────────── -->
    <section class="py-5" style="background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);">
        <div class="container text-center text-white py-3">
            <h3 class="fw-bold mb-3">Ready to find your lost item?</h3>
            <p class="mb-4" style="opacity:0.9;">Join hundreds of students and staff already using our platform.</p>
            <a href="{{ url('/register') }}" class="hero-btn hero-btn-white">
                <i class="fas fa-user-plus me-2"></i> Create Free Account
            </a>
        </div>
    </section>
@endsection
