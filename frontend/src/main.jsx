import React from 'react'
import ReactDOM from 'react-dom/client'
import './App.css'
import BibleSearch from './components/BibleSearch'
import BibleTrivia from './components/BibleTrivia'

// 1. Mount Search (Usually in your Navbar/Theme Header)
const searchEl = document.getElementById('bible-search-root');
if (searchEl) {
  ReactDOM.createRoot(searchEl).render(<BibleSearch />);
}

// 2. Mount Trivia (Usually in your Home/Theme Body)
const triviaEl = document.getElementById('bible-trivia-root');
if (triviaEl) {
  ReactDOM.createRoot(triviaEl).render(<BibleTrivia />);
}