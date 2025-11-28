const API = '../api';

document.getElementById('form').onsubmit = async (e) => {
    e.preventDefault();

    const inputs = Object.fromEntries(new FormData(e.target).entries());
    
;}