<!-- Modern Admin Sidebar Styles -->
<style>
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
    min-height: 100vh;
    position: fixed;
    top:0; left:0;
    padding:0;
    box-shadow:6px 0 25px rgba(0,0,0,0.2);
    overflow-y: auto;
}
.sidebar::-webkit-scrollbar { width: 6px; }
.sidebar::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
.sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 10px; }
.sidebar-header {
    padding: 30px 20px;
    background: rgba(0,0,0,0.2);
    border-bottom: 2px solid rgba(255,255,255,0.15);
    text-align: center;
    margin-bottom: 15px;
    position: relative;
    overflow: hidden;
}
.sidebar-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 4s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}
.sidebar-header h4 {
    color: #fff;
    font-weight: 800;
    font-size: 1.4rem;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    position: relative;
    z-index: 1;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}
.sidebar-header h4 i {
    font-size: 1.8rem;
    animation: iconBounce 2s ease-in-out infinite;
}
@keyframes iconBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
.sidebar-nav {
    padding: 15px 0;
}
.sidebar a {
    display: flex;
    align-items: center;
    gap: 15px;
    color: rgba(255,255,255,0.9);
    padding: 16px 25px;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 4px solid transparent;
    margin: 3px 10px;
    border-radius: 0 10px 10px 0;
    position: relative;
}
.sidebar a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 0;
    height: 100%;
    background: rgba(255,255,255,0.1);
    transition: width 0.3s ease;
    border-radius: 0 10px 10px 0;
}
.sidebar a:hover::before {
    width: 100%;
}
.sidebar a i {
    font-size: 1.2rem;
    width: 24px;
    text-align: center;
    transition: transform 0.3s ease;
    position: relative;
    z-index: 1;
}
.sidebar a:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
    border-left-color: #fbbf24;
    padding-left: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.sidebar a:hover i {
    transform: scale(1.2) rotate(5deg);
}
.sidebar a.active {
    background: linear-gradient(90deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.1) 100%);
    color: #fff;
    border-left-color: #fbbf24;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(251,191,36,0.3);
}
.sidebar a.active i {
    transform: scale(1.15);
}
.sidebar-divider {
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.2) 50%, transparent 100%);
    margin: 20px 25px;
}
.logout-btn {
    color: rgba(255,255,255,0.9);
    margin-top: 20px;
    border-top: 2px solid rgba(255,255,255,0.15);
    padding-top: 20px !important;
}
.logout-btn:hover {
    background: linear-gradient(90deg, rgba(220,53,69,0.4) 0%, rgba(220,53,69,0.2) 100%);
    color: #fff;
    border-left-color: #ef4444;
    box-shadow: 0 4px 12px rgba(220,53,69,0.3);
}
.logout-btn:hover i {
    animation: slideOut 0.5s ease;
}
@keyframes slideOut {
    0%, 100% { transform: translateX(0); }
    50% { transform: translateX(5px); }
}
.main-content { margin-left:280px; padding:40px; }
</style>
