class TicketingSystem {
    constructor() {
        this.API_BASE = 'http://localhost:5000/api';
        this.currentUser = null;
        this.tickets = [];
        this.currentStatusFilter = 'all';
        this.init();
    }

    init() {
        this.checkAuth();
        this.bindAuthEvents();
        this.bindAppEvents();
    }

    checkAuth() {
        const token = localStorage.getItem('token');
        if (token) {
            // Try to verify token by making a request
            this.verifyToken(token).then(user => {
                if (user) {
                    this.currentUser = user;
                    this.showMainApp();
                    this.loadTickets();
                } else {
                    this.showAuthOverlay();
                }
            }).catch(() => {
                this.showAuthOverlay();
            });
        } else {
            this.showAuthOverlay();
        }
    }

    async verifyToken(token) {
        try {
            const response = await fetch(`${this.API_BASE}/auth/me`, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                }
            });
            if (response.ok) {
                return await response.json();
            }
            return null;
        } catch (error) {
            console.error('Token verification failed:', error);
            return null;
        }
    }

    showAuthOverlay() {
        document.getElementById('authOverlay').style.display = 'flex';
        document.getElementById('mainApp').style.display = 'none';
    }

    showMainApp() {
        document.getElementById('authOverlay').style.display = 'none';
        document.getElementById('mainApp').style.display = 'block';
        document.getElementById('userName').textContent = this.currentUser.username;
        const roleBadge = document.getElementById('userRole');
        roleBadge.textContent = this.currentUser.role.toUpperCase();
        roleBadge.className = `role-badge ${this.currentUser.role === 'admin' ? 'admin-role' : 'user-role'}`;
    }

    bindAuthEvents() {
        // Tab switching
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                const targetTab = e.target.dataset.tab;
                document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
                e.target.classList.add('active');
                document.getElementById(`${targetTab}Form`).classList.add('active');
                this.clearMessages();
            });
        });

        // Login form
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        // Register form - THIS WAS MISSING!
        document.getElementById('registerForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegister();
        });
    }

    bindAppEvents() {
        // Logout
        document.getElementById('logoutBtn')?.addEventListener('click', () => {
            localStorage.removeItem('token');
            this.currentUser = null;
            this.showAuthOverlay();
        });

        // Ticket form
        document.getElementById('ticketForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.createTicket();
        });

        // Tab filtering
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.currentStatusFilter = e.target.dataset.status;
                this.renderTickets();
            });
        });
    }

    async handleRegister() {
        const username = document.getElementById('regUsername').value.trim();
        const email = document.getElementById('regEmail').value.trim();
        const password = document.getElementById('regPassword').value;

        try {
            const response = await fetch(`${this.API_BASE}/auth/register`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email, password })
            });

            const data = await response.json();

            if (response.ok) {
                localStorage.setItem('token', data.token);
                this.currentUser = data.user;
                this.showMainApp();
                this.showMessage('registerMessage', 'Account created successfully!', 'success');
                this.loadTickets();
            } else {
                this.showMessage('registerMessage', data.message || 'Registration failed', 'error');
            }
        } catch (error) {
            this.showMessage('registerMessage', 'Network error. Please try again.', 'error');
        }
    }

    async handleLogin() {
        const email = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPassword').value;

        try {
            const response = await fetch(`${this.API_BASE}/auth/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (response.ok) {
                localStorage.setItem('token', data.token);
                this.currentUser = data.user;
                this.showMainApp();
                this.showMessage('loginMessage', 'Login successful!', 'success');
                this.loadTickets();
            } else {
                this.showMessage('loginMessage', data.message || 'Login failed', 'error');
            }
        } catch (error) {
            this.showMessage('loginMessage', 'Network error. Please try again.', 'error');
        }
    }

    clearMessages() {
        document.querySelectorAll('.message').forEach(msg => {
            msg.textContent = '';
            msg.className = 'message';
        });
    }

    showMessage(messageId, text, type) {
        const messageEl = document.getElementById(messageId);
        messageEl.textContent = text;
        messageEl.className = `message ${type}`;
    }

    async loadTickets() {
        try {
            const response = await fetch(`${this.API_BASE}/tickets`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            
            if (response.ok) {
                this.tickets = await response.json();
                this.renderTickets();
                this.updateStats();
            }
        } catch (error) {
            console.error('Failed to load tickets:', error);
        }
    }

    async createTicket() {
        const formData = {
            title: document.getElementById('title').value,
            description: document.getElementById('description').value,
            priority: document.getElementById('priority').value,
            category: document.getElementById('category').value
        };

        try {
            const response = await fetch(`${this.API_BASE}/tickets`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(formData)
            });

            if (response.ok) {
                this.resetForm();
                await this.loadTickets();
                this.showNotification('Ticket created successfully!', 'success');
            }
        } catch (error) {
            this.showNotification('Failed to create ticket', 'error');
        }
    }

    resetForm() {
        document.getElementById('ticketForm').reset();
    }

    renderTickets() {
        const ticketsList = document.getElementById('ticketsList');
        const filteredTickets = this.currentStatusFilter === 'all' 
            ? this.tickets 
            : this.tickets.filter(t => t.status === this.currentStatusFilter);

        if (filteredTickets.length === 0) {
            ticketsList.innerHTML = '<div class="no-tickets">No tickets found</div>';
            return;
        }

        ticketsList.innerHTML = filteredTickets.map(ticket => this.createTicketCard(ticket)).join('');
    }

    createTicketCard(ticket) {
        const date = new Date(ticket.updatedAt).toLocaleDateString();
        const priorityClass = `priority-${ticket.priority}`;
        const statusClass = `status-${ticket.status}`;
        
        return `
            <div class="ticket-card">
                <div class="ticket-header">
                    <div>
                        <h3 class="ticket-title">${this.escapeHtml(ticket.title)}</h3>
                        <div class="ticket-meta">
                            <span class="badge ${priorityClass}">${ticket.priority.toUpperCase()}</span>
                            <span class="badge ${statusClass}">${ticket.status.replace('-', ' ').toUpperCase()}</span>
                            <span class="badge">${ticket.category}</span>
                        </div>
                    </div>
                    <div class="ticket-id">#${ticket._id.slice(-6)}</div>
                </div>
                <p class="ticket-description">${this.escapeHtml(ticket.description)}</p>
                <div class="ticket-date">Updated: ${date}</div>
            </div>
        `;
    }

    updateStats() {
        const openCount = this.tickets.filter(t => t.status === 'open').length;
        const progressCount = this.tickets.filter(t => t.status === 'in-progress').length;
        const closedCount = this.tickets.filter(t => t.status === 'closed').length;

        document.getElementById('openCount').textContent = openCount;
        document.getElementById('progressCount').textContent = progressCount;
        document.getElementById('closedCount').textContent = closedCount;
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    showNotification(message, type) {
        // Simple notification - you can enhance this
        console.log(`${type.toUpperCase()}: ${message}`);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new TicketingSystem();
});