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
  // booking trend range (30, 90, 1Y)
  let bookingTrendRange = '30';
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

  // Poll realtime data and update charts
  async function pollRealtime(){
    try{
      const url = '/superadmin/dashboard/realtime' + (bookingTrendRange ? '?range=' + encodeURIComponent(bookingTrendRange) : '');
      const res = await fetch(url, { headers:{ 'Accept':'application/json' }, cache: 'no-store' });
      if(!res.ok) return;
      const d = await res.json();

      // Booking trend
      if(window.__bookingTrendChart && d.bookingTrend){
        window.__bookingTrendChart.data.labels = Array.isArray(d.bookingTrend.labels) ? d.bookingTrend.labels : window.__bookingTrendChart.data.labels;
        window.__bookingTrendChart.data.datasets[0].data = Array.isArray(d.bookingTrend.values) ? d.bookingTrend.values : window.__bookingTrendChart.data.datasets[0].data;
        window.__bookingTrendChart.update();
      }

      // Bookings by department
      if(window.__bookingsDeptBarChart && d.bookingsByDepartment){
        const obj = d.bookingsByDepartment || {};
        const labels = Object.keys(obj);
        const values = labels.map(k => Number(obj[k] || 0));
        window.__bookingsDeptBarChart.data.labels = labels;
        if(window.__bookingsDeptBarChart.data.datasets && window.__bookingsDeptBarChart.data.datasets[0]){
          window.__bookingsDeptBarChart.data.datasets[0].data = values;
        }
        window.__bookingsDeptBarChart.update();
      }

      // Booking status donut (take up to first 3 values)
      if(window.__bookingStatusDonutChart && d.bookingStatus){
        const vals = Object.values(d.bookingStatus || {}).map(v=>Number(v||0));
        const dataArr = [vals[0]||0, vals[1]||0, vals[2]||0];
        window.__bookingStatusDonutChart.data.datasets[0].data = dataArr;
        window.__bookingStatusDonutChart.update();
      }

      // Invoices donut
      if(window.__invoiceDonutChart && d.invoices){
        const inv = d.invoices || {};
        window.__invoiceDonutChart.data.datasets[0].data = [Number(inv.paid||0), Number(inv.unpaid||0), Number(inv.cancel||0)];
        window.__invoiceDonutChart.update();
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        setVal('invPaid', Number(inv.paid||0)); setVal('invUnpaid', Number(inv.unpaid||0)); setVal('invCancel', Number(inv.cancel||0));
      }

      // Attendance donut
      if(window.__attendanceDonutChart && d.attendance){
        const a = d.attendance || {};
        const present = Number(a.present||0); const absent = Number(a.absent||0); const late = Number(a.late||0);
        window.__attendanceDonutChart.data.datasets[0].data = [present, absent, late];
        window.__attendanceDonutChart.update();
        const setTxt = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent = `${val}`; };
        setTxt('attPresent', present); setTxt('attAbsent', absent); setTxt('attLate', late);
      }

      // Analyst workloads: update global data and re-render using existing renderer
      if(d.analystWorkloadAll) window.analystWorkloadAll = d.analystWorkloadAll;
      if(d.analystWorkload30) window.analystWorkload30 = d.analystWorkload30;
      if(d.analystWorkload90) window.analystWorkload90 = d.analystWorkload90;
      if(window.__renderAnalystChart){
        // determine which range is active
        const activeBtn = document.querySelector('.workload-range-toggle .btn.active');
        const range = activeBtn ? activeBtn.getAttribute('data-range') : '30';
        if(range === '30') window.__renderAnalystChart(window.analystWorkload30 || []);
        else if(range === '90') window.__renderAnalystChart(window.analystWorkload90 || []);
        else window.__renderAnalystChart(window.analystWorkloadAll || []);
      }

      // update bookingTrendRange button active state (in case server default differs)
      try{
        const activeBtn = document.querySelector('.booking-trend-range-toggle .btn[data-range="' + bookingTrendRange + '"]');
        if(activeBtn){ document.querySelectorAll('.booking-trend-range-toggle .btn').forEach(b=>b.classList.remove('active')); activeBtn.classList.add('active'); }
      }catch(e){}

    }catch(e){
      // silent
      console.error('Realtime poll error', e);
    }
  }

  // start polling every 10 seconds
  setInterval(pollRealtime, 10000);
  // run once after init
  setTimeout(pollRealtime, 2000);

  // Booking trend range buttons
  document.querySelectorAll('.booking-trend-range-toggle .btn').forEach(btn=>{
    btn.addEventListener('click', function(){
      document.querySelectorAll('.booking-trend-range-toggle .btn').forEach(b=>b.classList.remove('active'));
      this.classList.add('active');
      bookingTrendRange = this.getAttribute('data-range') || '30';
      // immediately poll with new range
      pollRealtime();
    });
  });

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
    window.__customersDonutChart = new Chart(donut, {
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
    const labels = Array.from({length: 30}, (_,i)=> `${i+1}`);
    const data = labels.map(()=> rnd(10, 40));
    window.__bookingTrendChart = new Chart(bookingTrend, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Bookings',
          data,
          borderColor: '#ff8a26',
          backgroundColor: 'rgba(255,138,38,0.15)',
          tension: 0.35,
          fill: true,
          pointRadius: 0
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f1f1' } } }
      }
    });
  }

  // Booking Status Donut
  if(bookingStatusDonut){
    window.__bookingStatusDonutChart = new Chart(bookingStatusDonut, {
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
    window.__dispatchBarChart = new Chart(dispatchBar, {
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
    window.__attendanceDonutChart = new Chart(attendanceDonut, {
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
    window.__invoiceDonutChart = invChart;

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

      // expose renderer and chart instance for realtime updates
      window.__renderAnalystChart = renderAnalystChart;
      window.__analystWorkloadChart = analystChart;

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
