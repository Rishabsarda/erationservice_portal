// assets/js/main.js

/**
 * Fetch and update shop status and stock levels on public dashboard
 */
function fetchShopStatus() {
    const isSubdir = window.location.pathname.includes('/citizen/') || window.location.pathname.includes('/shopkeeper/');
    const apiPath = isSubdir ? '../api/get_status.php' : 'api/get_status.php';
    
    fetch(apiPath)
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Update UI elements if they exist
            const statusBadge = document.getElementById('shopStatusBadge');
            if (statusBadge) {
                if (data.is_open == 1) {
                    statusBadge.className = 'status-badge status-open';
                    statusBadge.innerHTML = '🟢 SHOP OPEN';
                } else {
                    statusBadge.className = 'status-badge status-closed';
                    statusBadge.innerHTML = '🔴 SHOP CLOSED';
                }
            }

            // Update Stock Progress bars and text
            updateStockUI('rice', data.stock.rice, 5000); // Admin max logic e.g. 5000
            updateStockUI('wheat', data.stock.wheat, 5000);
            updateStockUI('sugar', data.stock.sugar, 1000);
            updateStockUI('kerosene', data.stock.kerosene, 1000);

            const lastUpdated = document.getElementById('lastUpdatedText');
            if(lastUpdated) lastUpdated.textContent = data.stock.last_updated;
        }
    })
    .catch(err => console.error('Error fetching status:', err));
}

function updateStockUI(item, current, max) {
    const textEl = document.getElementById(item + '-qty');
    const barEl = document.getElementById(item + '-bar');
    
    if (textEl && barEl) {
        textEl.textContent = current;
        let percentage = (current / max) * 100;
        if(percentage > 100) percentage = 100;
        barEl.style.width = percentage + '%';
        
        // Color coding for low stock
        if (current < 50) {
            barEl.style.background = 'var(--danger-color)';
        } else if (current < 150) {
            barEl.style.background = 'var(--warning-color)';
        } else {
            barEl.style.background = 'linear-gradient(90deg, var(--secondary-color), var(--primary-color))';
        }
    }
}

// Auto refresh status every 60 seconds if we are on dashboard/home
if(document.body.classList.contains('auto-refresh-status')) {
    fetchShopStatus(); // Initial fetch
    setInterval(fetchShopStatus, 60000); // Every 60s
}

/**
 * Shopkeeper toggle status function
 */
function toggleShopStatus() {
    const btn = document.getElementById('toggleShopBtn');
    if(!btn) return;

    btn.disabled = true;
    btn.textContent = 'Updating...';

    fetch('../api/toggle_shop.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            if(data.is_open == 1) {
                btn.className = 'btn btn-danger btn-block';
                btn.innerHTML = '🔴 Close Shop Now';
                document.getElementById('shopCurrentStatus').innerHTML = '<span class="status-badge status-open">🟢 SHOP OPEN</span>';
            } else {
                btn.className = 'btn btn-success btn-block';
                btn.innerHTML = '🟢 Open Shop Now';
                document.getElementById('shopCurrentStatus').innerHTML = '<span class="status-badge status-closed">🔴 SHOP CLOSED</span>';
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(err => console.error(err))
    .finally(() => {
        btn.disabled = false;
    });
}

/**
 * Citizen Collect Ration (Fingerprint Simulation)
 */
function collectRation() {
    const btn = document.getElementById('collectRationBtn');
    const modal = document.getElementById('confirmationModal');
    
    if(!btn) return;

    // Simulate scanning
    btn.classList.add('scanning');
    btn.innerHTML = '<span class="fingerprint-icon">⏳</span><br>Scanning Fingerprint...<div class="scan-line"></div>';
    
    setTimeout(() => {
        fetch('../api/collect_ration.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            btn.classList.remove('scanning');
            
            if(data.success) {
                btn.innerHTML = '<span class="fingerprint-icon">✅</span><br>Ration Collected Successfully';
                btn.style.background = '#059669'; // Darker green
                btn.disabled = true;
                
                // Show modal details
                document.getElementById('modalMessage').innerHTML = `
                    <h3 style="color:var(--success-color); margin-bottom:1rem;">Success!</h3>
                    <p>Your ration has been distributed:</p>
                    <ul style="text-align:left; margin-left:2rem; margin-top:1rem; color:var(--text-muted)">
                        <li>Rice: ${data.details.rice} kg</li>
                        <li>Wheat: ${data.details.wheat} kg</li>
                        <li>Sugar: ${data.details.sugar} kg</li>
                        <li>Kerosene: ${data.details.kerosene} L</li>
                    </ul>
                `;
                modal.classList.add('active');
            } else {
                btn.innerHTML = '<span class="fingerprint-icon">👆</span><br>Collect My Ration';
                alert('Failed: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            btn.classList.remove('scanning');
            btn.innerHTML = '<span class="fingerprint-icon">👆</span><br>Collect My Ration';
            alert('Network error. Please try again.');
        });
    }, 2000); // 2 second scan animation
}

function closeModal() {
    document.getElementById('confirmationModal').classList.remove('active');
    window.location.reload(); // Reload to update dashboard stats
}

/**
 * Time Slot Booking Logic
 */
function fetchTimeSlots() {
    const isSubdir = window.location.pathname.includes('/citizen/') || window.location.pathname.includes('/shopkeeper/');
    const apiPath = isSubdir ? '../api/get_slot_counts.php' : 'api/get_slot_counts.php';
    
    const dateInput = document.getElementById('slotDate');
    if(!dateInput) return;
    const date = dateInput.value;

    fetch(`${apiPath}?date=${date}`)
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            renderTimeSlots(data.counts, data.date, data.my_booking);
        } else {
            document.getElementById('slotsContainer').innerHTML = `<p style="color:red;">Error loading slots.</p>`;
        }
    })
    .catch(err => console.error(err));
}

function renderTimeSlots(counts, date, my_booking) {
    const container = document.getElementById('slotsContainer');
    let html = '';

    if (my_booking) {
        html += `<div class="alert alert-success" style="margin-bottom:1rem; padding: 0.8rem; font-size: 0.9rem;">
            <strong>Your current booking:</strong><br>
            Date: ${my_booking.slot_date}<br>
            Time: ${my_booking.time_slot}
        </div>`;
    }

    html += '<div style="display:flex; flex-direction:column; gap:0.8rem;">';

    for(let slot in counts) {
        let count = counts[slot];
        let isMyCurrentSlot = (my_booking && my_booking.slot_date == date && my_booking.time_slot == slot);
        
        // Calculate congestion color
        let badgeColor = 'var(--success-color)';
        if(count > 10) badgeColor = 'var(--warning-color)';
        if(count > 20) badgeColor = 'var(--danger-color)';
        
        let buttonText = isMyCurrentSlot ? 'Booked' : (my_booking ? 'Reschedule Here' : 'Book');
        let buttonDisabled = isMyCurrentSlot ? 'disabled' : '';
        let buttonClass = isMyCurrentSlot ? 'btn-secondary' : 'btn-primary';

        html += `
            <div style="display:flex; justify-content:space-between; align-items:center; border:1px solid #e2e8f0; padding:1rem; border-radius:8px; background:white;">
                <div>
                    <strong style="display:block; font-size:0.95rem;">${slot}</strong>
                    <span style="font-size:0.8rem; color:${badgeColor}; font-weight:bold;">${count} people booked</span>
                </div>
                <button class="btn ${buttonClass}" style="padding:0.4rem 0.8rem; font-size:0.85rem;" ${buttonDisabled} onclick="bookTimeSlot('${date}', '${slot}')">
                    ${buttonText}
                </button>
            </div>
        `;
    }

    html += '</div>';
    container.innerHTML = html;
}

function bookTimeSlot(date, timeSlot) {
    if(!confirm(`Are you sure you want to book/reschedule to ${date} at ${timeSlot}?`)) return;

    fetch('../api/book_slot.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ date: date, time_slot: timeSlot })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            fetchTimeSlots(); // Refresh the counts and my_booking view
        } else {
            alert('Failed: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error. Please try again.');
    });
}

// Call on page load if elements exist
if(document.getElementById('slotDate')) {
    fetchTimeSlots();
}

/**
 * Admin Manual Ration Collection Logic
 */
function lookupCitizen() {
    const rcInput = document.getElementById('lookupRationCard');
    if(!rcInput || !rcInput.value.trim()) {
        alert("Please enter a ration card number.");
        return;
    }

    const rc = rcInput.value.trim();
    const resultDiv = document.getElementById('lookupResult');
    const nameSpan = document.getElementById('lookupName');
    const familySpan = document.getElementById('lookupFamily');
    const typeSpan = document.getElementById('lookupType');
    const statusDiv = document.getElementById('lookupStatus');
    const collectBtn = document.getElementById('adminCollectBtn');

    // Reset UI
    resultDiv.style.display = 'none';
    collectBtn.style.display = 'none';

    fetch(`../api/admin_lookup_citizen.php?rc=${encodeURIComponent(rc)}`)
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            resultDiv.style.display = 'block';
            nameSpan.textContent = data.user.name;
            familySpan.textContent = data.user.family_members;
            typeSpan.textContent = data.user.card_type;
            
            if(data.already_collected) {
                statusDiv.innerHTML = `<span style="color: var(--danger-color); font-weight:bold;">Already collected on ${data.already_collected}</span>`;
            } else {
                statusDiv.innerHTML = `<span style="color: var(--success-color); font-weight:bold;">Ready to collect</span>`;
                collectBtn.style.display = 'block';
                // Store current RC on button for next action
                collectBtn.dataset.rc = rc;
            }
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error(err));
}

function adminCollectRation() {
    const collectBtn = document.getElementById('adminCollectBtn');
    const rc = collectBtn.dataset.rc;
    
    if(!rc) return;
    if(!confirm("Are you sure you want to mark ration as collected for this citizen?")) return;

    collectBtn.disabled = true;
    collectBtn.textContent = 'Processing...';

    fetch('../api/admin_collect_ration.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ration_card_no: rc })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            window.location.reload(); // Reload to update distributions and stock immediately
        } else {
            alert('Failed: ' + data.message);
            collectBtn.disabled = false;
            collectBtn.textContent = 'Mark as Collected';
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error. Please try again.');
        collectBtn.disabled = false;
        collectBtn.textContent = 'Mark as Collected';
    });
}

function viewSlotDetails(timeSlot) {
    const listContainer = document.getElementById('slotDetailsList');
    const wrapper = document.getElementById('slotDetailsContainer');
    const timeSpan = document.getElementById('slotDetailsTime');
    
    if(!listContainer || !wrapper) return;
    
    timeSpan.textContent = timeSlot;
    listContainer.innerHTML = '<p style="color:var(--text-muted); font-size:0.9rem;">Loading citizens...</p>';
    wrapper.style.display = 'block';

    fetch(`../api/admin_get_slot_users.php?time_slot=${encodeURIComponent(timeSlot)}`)
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            if(data.users.length === 0) {
                listContainer.innerHTML = '<p style="color:var(--text-muted); font-size:0.9rem;">No one booked for this slot.</p>';
            } else {
                let html = '<ul style="list-style:none; padding:0; margin:0; font-size:0.9rem;">';
                data.users.forEach(u => {
                    html += `<li style="padding:0.5rem 0; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between;">
                        <span>${u.name}</span>
                        <strong style="color:var(--primary-color);">${u.ration_card_no}</strong>
                    </li>`;
                });
                html += '</ul>';
                listContainer.innerHTML = html;
            }
        } else {
            listContainer.innerHTML = `<p style="color:red; font-size:0.9rem;">${data.message}</p>`;
        }
    })
    .catch(err => {
        console.error(err);
        listContainer.innerHTML = `<p style="color:red; font-size:0.9rem;">Network error.</p>`;
    });
}
