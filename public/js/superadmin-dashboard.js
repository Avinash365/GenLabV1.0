/* Simple demo data generator and Chart.js setup for the Superadmin dashboard */
(function(){
  if(typeof Chart === 'undefined') return;

  const apiUrl = '/superadmin/dashboard/invoice-payment-chart';

  const ctx = document.getElementById('salesPurchaseChart');
  const donut = document.getElementById('customersDonut');
  const bookingTrend = document.getElementById('bookingTrend');
  const bookingStatusDonut = document.getElementById('bookingStatusDonut');
  const dispatchBar = document.getElementById('dispatchBar');
  const attendanceDonut = document.getElementById('attendanceDonut');
  const invoiceDonut = document.getElementById('invoiceDonut');
  const analystWorkloadChart = document.getElementById('analystWorkloadChart');
  if(!ctx) return; // page safety

  function rnd(min,max){return Math.round(Math.random()*(max-min)+min)}

  const gradientSales = ctx.getContext('2d').createLinearGradient(0,0,0,300);
  gradientSales.addColorStop(0,'#ff8a26');
  gradientSales.addColorStop(1,'#ffb26b');

  const gradientPurchase = ctx.getContext('2d').createLinearGradient(0,0,0,300);
  gradientPurchase.addColorStop(0,'#ffe6cc');
  gradientPurchase.addColorStop(1,'#ffd9b3');

  let currentRange = '1Y';
  let labels = [];
  let sales = [];
  let purchase = [];

  const barChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Payment (Done)',
          backgroundColor: gradientSales,
          data: sales,
          borderRadius: 6,
          barThickness: 'flex',
          categoryPercentage: 0.8,
          barPercentage: 0.6,
          stack: 'invoice-stack'
        },
        {
          label: 'Remaining',
          backgroundColor: gradientPurchase,
          data: purchase,
          borderRadius: 6,
          barThickness: 'flex',
          categoryPercentage: 0.8,
          barPercentage: 0.6,
          stack: 'invoice-stack'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx)=>{
              const value = Number(ctx.parsed.y || 0);
              return `${ctx.dataset.label}: ₹ ${value.toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
            }
          }
        }
      },
      scales: {
        x: { stacked: true, grid: { display: false } },
        y: {
          stacked: true,
          ticks: {
            callback: (val)=> `₹ ${val}`
          }
        }
      }
    }
  });

  function setTotals(invoiceTotal, paymentTotal){
    const elInvoice = document.getElementById('totalSales');
    const elPayment = document.getElementById('totalPurchase');
    if(elInvoice) elInvoice.textContent = `₹ ${Number(invoiceTotal || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
    if(elPayment) elPayment.textContent = `₹ ${Number(paymentTotal || 0).toLocaleString(undefined, { maximumFractionDigits: 2 })}`;
  }

  async function loadRange(range){
    const url = `${apiUrl}?range=${encodeURIComponent(range)}`;
    const res = await fetch(url, {
      headers: { 'Accept': 'application/json' },
      cache: 'no-store'
    });
    if(!res.ok) throw new Error(`Failed to load chart data (${res.status})`);
    return await res.json();
  }

  async function setRange(range){
    currentRange = range;
    try {
      const data = await loadRange(range);
      barChart.data.labels = Array.isArray(data.labels) ? data.labels : [];
      const invoiceArr = Array.isArray(data.invoice) ? data.invoice : [];
      const paymentArr = Array.isArray(data.payment) ? data.payment : [];
      const paidArr = paymentArr.map(v => Number(v || 0));
      const remainingArr = invoiceArr.map((inv, i) => {
        const invoiceVal = Number(inv || 0);
        const paidVal = Number(paidArr[i] || 0);
        return Math.max(0, invoiceVal - paidVal);
      });
      barChart.data.datasets[0].data = paidArr;
      barChart.data.datasets[1].data = remainingArr;
      barChart.update();
      setTotals(data?.totals?.invoice, data?.totals?.payment);
    } catch (e) {
      // Fallback to demo data if API is unavailable
      const fallbackLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
      const demoInvoice = fallbackLabels.map(()=> rnd(5000, 25000));
      const demoPayment = demoInvoice.map(v=> rnd(Math.max(0, v-8000), v));
      barChart.data.labels = fallbackLabels;
      const demoRemaining = demoInvoice.map((inv, i)=> Math.max(0, Number(inv||0) - Number(demoPayment[i]||0)));
      barChart.data.datasets[0].data = demoPayment;
      barChart.data.datasets[1].data = demoRemaining;
      barChart.update();
      setTotals(demoInvoice.reduce((a,b)=>a+b,0), demoPayment.reduce((a,b)=>a+b,0));
    }
  }

  document.querySelectorAll('.range-toggle .btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.querySelectorAll('.range-toggle .btn').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      setRange(btn.getAttribute('data-range'));
    })
  })

  // Customers donut
  if(donut){
    new Chart(donut, {
      type: 'doughnut',
      data: {
        labels: ['First Time','Returning','Inactive'],
        datasets: [{
          data: [55, 35, 10],
          backgroundColor: ['#2bb673','#ff8a26','#e0e0e0'],
          borderWidth: 0,
          cutout: '70%'
        }]
      },
      options: { plugins: { legend: { display: false } }, maintainAspectRatio: false }
    });
  }

  // default select 1Y button
  const defaultBtn = document.querySelector('.range-toggle .btn[data-range="1Y"]');
  if(defaultBtn){
    document.querySelectorAll('.range-toggle .btn').forEach(b=>b.classList.remove('active'));
    defaultBtn.classList.add('active');
  }

  // initial load
  setRange('1Y');

  // ============= Additional Widgets Aligned to Sidebar =============
  // Booking Trend (line chart)
  if(bookingTrend){
    (function renderBookingTrend(){
      let currentDays = '30';

      async function fetchTrend(days){
        try{
          const url = '/superadmin/dashboard/booking-trend?days=' + encodeURIComponent(days);
          const res = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
          if(!res.ok) throw new Error('Network');
          return await res.json();
        } catch(e){ return null; }
      }

      let chartInstance = null;

      async function draw(days){
        const payload = await fetchTrend(days);
        let labels = [], data = [];
        if(payload && Array.isArray(payload.labels) && Array.isArray(payload.data)){
          labels = payload.labels;
          data = payload.data.map(v => Number(v || 0));
        } else {
          labels = Array.from({length: 30}, (_,i)=> `${i+1}`);
          data = labels.map(()=> rnd(10, 40));
        }

        const cfg = {
          type: 'line',
          data: { labels, datasets: [{ label: 'Bookings', data, borderColor: '#ff8a26', backgroundColor: 'rgba(255,138,38,0.15)', tension: 0.35, fill: true, pointRadius: 0 }] },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f1f1' } } } }
        };

        if(chartInstance){ chartInstance.data = cfg.data; chartInstance.options = cfg.options; chartInstance.update(); }
        else { chartInstance = new Chart(bookingTrend, cfg); }

        // update label
        const labelEl = document.getElementById('bookingRangeLabel');
        if(labelEl){
          if(String(days) === 'all') labelEl.textContent = 'All time';
          else labelEl.textContent = `Last ${String(days)} days`;
        }
      }

      // initial draw
      draw(currentDays);

      // wire up toggle buttons
      document.querySelectorAll('.booking-range-toggle [data-days]').forEach(btn=>{
        btn.addEventListener('click', function(){
          document.querySelectorAll('.booking-range-toggle .btn').forEach(b=>b.classList.remove('active'));
          this.classList.add('active');
          currentDays = this.getAttribute('data-days') || '30';
          draw(currentDays);
        });
      });
    })();
  }

  // Booking Status Donut
  if(bookingStatusDonut){
    new Chart(bookingStatusDonut, {
      type: 'doughnut',
      data: {
        labels: ['Pending','Completed','Processing'],
        datasets: [{
          data: [35, 45, 20],
          backgroundColor: ['#ff8a26','#2bb673','#ffc107'],
          borderWidth: 0,
          cutout: '70%'
        }]
      },
      options: { plugins: { legend: { display: false } }, maintainAspectRatio: false }
    });
  }

  // Report Dispatch (stacked bar for modes: Email vs Print)
  if(dispatchBar){
    const labels = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    new Chart(dispatchBar, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'Email', backgroundColor: '#2bb673', data: labels.map(()=> rnd(10,25)), stack: 'd' },
          { label: 'Printed', backgroundColor: '#ff8a26', data: labels.map(()=> rnd(5,15)), stack: 'd' }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { x: { stacked: true, grid:{ display:false } }, y: { stacked: true } }
      }
    });
  }

  // Attendance Donut
  if(attendanceDonut){
    const present = rnd(60,85), absent = rnd(5,20), late = 100 - present - absent;
    new Chart(attendanceDonut, {
      type: 'doughnut',
      data: { labels: ['Present','Absent','Late'], datasets: [{ data: [present, absent, late], backgroundColor: ['#2bb673','#dc3545','#ffc107'], borderWidth: 0, cutout: '70%' }] },
      options: { plugins: { legend: { display: false } }, maintainAspectRatio: false }
    });
    const setTxt = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent = `${val}%`; };
    setTxt('attPresent', present); setTxt('attAbsent', absent); setTxt('attLate', late);
  }

  // Accounts - Invoices Donut
  if(invoiceDonut){
    const invChart = new Chart(invoiceDonut, {
      type: 'doughnut',
      data: {
        labels: ['Paid', 'Unpaid', 'Cancel'],
        datasets: [{
          data: [0, 0, 0],
          backgroundColor: ['#2bb673', '#6c757d', '#dc3545'],
          borderWidth: 0,
          cutout: '70%'
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        maintainAspectRatio: false
      }
    });

    fetch('/superadmin/dashboard/accounts-invoices-chart')
      .then(r => r.json())
      .then(d => {
         const paid = Number(d.paid || 0);
         const unpaid = Number(d.unpaid || 0);
         // Prefer explicit 'cancel' field; fall back to common variants or 'overdue'
         const cancel = Number(d.cancel ?? d.canceled ?? d.cancelled ?? d.overdue ?? 0);

         invChart.data.datasets[0].data = [paid, unpaid, cancel];
         invChart.update();

         const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
         setVal('invPaid', paid);
         setVal('invUnpaid', unpaid);
         setVal('invCancel', cancel);
      })
      .catch(e => console.error('Accounts chart error:', e));
  }

  // Analysts Workload (horizontal bar) with 30/90/all toggles
  if(analystWorkloadChart){
    let analystChart = null;

    function renderAnalystChart(dataset){
      const data = Array.isArray(dataset) ? dataset : [];
      const names = data.map(x => x.name);
      const totals = data.map(x => Number(x.count || 0));
      const overdue = data.map(x => Number(x.overdue || 0));
      const remaining = totals.map((t,i) => Math.max(0, t - (overdue[i] || 0)));

      // update global overdue display (sum of overdue in current dataset) if element exists
      const overdueTotal = overdue.reduce((s,v)=> s + (Number(v)||0), 0);
      const overdueEl = document.getElementById('analystOverdueCount');
      if(overdueEl) overdueEl.textContent = Number(overdueTotal).toLocaleString();

      // if any overdue values present, render stacked overdue+remaining, otherwise render single series
      const hasOverdue = overdue.some(v=> v > 0);

      const cfg = {
        type: 'bar',
        data: {
          labels: names,
          datasets: hasOverdue ? [
            { label: 'Overdue', data: overdue, backgroundColor: '#e15759' },
            { label: 'Remaining', data: remaining, backgroundColor: '#6f42c1' }
          ] : [ { label: 'Samples', data: totals, backgroundColor: '#6f42c1' } ]
        },
        options: {
          indexAxis: 'y',
          responsive:true,
          maintainAspectRatio:false,
          plugins:{ legend:{ position: 'top' } },
          scales: {
            x: { stacked: hasOverdue, grid:{ color:'#f1f1f1' }, beginAtZero: true },
            y: { stacked: hasOverdue, grid:{ display:false } }
          }
        }
      };

      if(analystChart){
        analystChart.data = cfg.data;
        analystChart.options = cfg.options;
        analystChart.update();
      } else {
        analystChart = new Chart(analystWorkloadChart, cfg);
      }
    }

    // Choose default dataset: 30 days if available, else all
    const defaultData = window.analystWorkload30 ?? window.analystWorkloadAll ?? [];
    renderAnalystChart(defaultData);

    // Attach toggle handlers for workload-specific buttons
    document.querySelectorAll('.workload-range-toggle .btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.workload-range-toggle .btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const range = btn.getAttribute('data-range');
        if(range === '30') renderAnalystChart(window.analystWorkload30 ?? []);
        else if(range === '90') renderAnalystChart(window.analystWorkload90 ?? []);
        else renderAnalystChart(window.analystWorkloadAll ?? []);
      });
    });
  }
})();
