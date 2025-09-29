/* Simple demo data generator and Chart.js setup for the Superadmin dashboard */
(function(){
  if(typeof Chart === 'undefined') return;

  const ctx = document.getElementById('salesPurchaseChart');
  const donut = document.getElementById('customersDonut');
  const bookingTrend = document.getElementById('bookingTrend');
  const bookingStatusDonut = document.getElementById('bookingStatusDonut');
  const dispatchBar = document.getElementById('dispatchBar');
  const attendanceDonut = document.getElementById('attendanceDonut');
  const invoiceDonut = document.getElementById('invoiceDonut');
  const analystWorkloadChart = document.getElementById('analystWorkloadChart');
  if(!ctx) return; // page safety

  const ranges = {
    '1D': { labels: ['2 am','4 am','6 am','8 am','10 am','12 am','14 pm','16 pm','18 pm','20 pm','22 pm','24 pm'], points: 12 },
    '1W': { labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], points: 7 },
    '1M': { labels: Array.from({length: 30}, (_,i)=>`${i+1}`), points: 30 },
    '3M': { labels: Array.from({length: 12}, (_,i)=>`W${i+1}`), points: 12 },
    '6M': { labels: ['Jan','Feb','Mar','Apr','May','Jun'], points: 6 },
    '1Y': { labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'], points: 12 },
  };

  function rnd(min,max){return Math.round(Math.random()*(max-min)+min)}
  function buildData(points){
    const sales = Array.from({length: points}, ()=>rnd(5,25));
    const purchase = sales.map(v=> rnd(v+10, v+40)); // keep purchase taller to match look
    return { sales, purchase };
  }

  function sum(arr){return arr.reduce((a,b)=>a+b,0)}

  const gradientSales = ctx.getContext('2d').createLinearGradient(0,0,0,300);
  gradientSales.addColorStop(0,'#ff8a26');
  gradientSales.addColorStop(1,'#ffb26b');

  const gradientPurchase = ctx.getContext('2d').createLinearGradient(0,0,0,300);
  gradientPurchase.addColorStop(0,'#ffe6cc');
  gradientPurchase.addColorStop(1,'#ffd9b3');

  let currentRange = '1Y';
  let { labels } = ranges[currentRange];
  let { sales, purchase } = buildData(labels.length);

  const barChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Sales',
          backgroundColor: gradientSales,
          data: sales,
          borderRadius: 6,
          barThickness: 'flex',
          categoryPercentage: 0.8,
          barPercentage: 0.6,
          stack: 'stack-0'
        },
        {
          label: 'Purchase',
          backgroundColor: gradientPurchase,
          data: purchase,
          borderRadius: 6,
          barThickness: 'flex',
          categoryPercentage: 0.8,
          barPercentage: 0.6,
          stack: 'stack-0'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: { label: (ctx)=>`${ctx.dataset.label}: ${ctx.parsed.y}K` }
        }
      },
      scales: {
        x: { stacked: true, grid: { display: false } },
        y: {
          stacked: true,
          ticks: {
            callback: (val)=> `${val}K`
          }
        }
      }
    }
  });

  function updateTotals(){
    const totalS = sum(barChart.data.datasets[0].data);
    const totalP = sum(barChart.data.datasets[1].data);
    const elS = document.getElementById('totalSales');
    const elP = document.getElementById('totalPurchase');
    if(elS) elS.textContent = `${(totalS).toLocaleString()}K`;
    if(elP) elP.textContent = `${(totalP).toLocaleString()}K`;
  }

  function setRange(range){
    currentRange = range;
    const { labels, points } = ranges[range];
    const d = buildData(points);
    barChart.data.labels = labels;
    barChart.data.datasets[0].data = d.sales;
    barChart.data.datasets[1].data = d.purchase;
    barChart.update();
    updateTotals();
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

  // initial totals
  updateTotals();
  // default select 1Y button
  const defaultBtn = document.querySelector('.range-toggle .btn[data-range="1Y"]');
  if(defaultBtn){
    document.querySelectorAll('.range-toggle .btn').forEach(b=>b.classList.remove('active'));
    defaultBtn.classList.add('active');
  }

  // ============= Additional Widgets Aligned to Sidebar =============
  // Booking Trend (line chart)
  if(bookingTrend){
    const labels = Array.from({length: 30}, (_,i)=> `${i+1}`);
    const data = labels.map(()=> rnd(10, 40));
    new Chart(bookingTrend, {
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
    const paid = rnd(50,70), unpaid = rnd(20,35), overdue = 100 - paid - unpaid;
    new Chart(invoiceDonut, { type:'doughnut', data:{ labels:['Paid','Unpaid','Overdue'], datasets:[{ data:[paid,unpaid,overdue], backgroundColor:['#2bb673','#6c757d','#dc3545'], borderWidth:0, cutout:'70%' }] }, options:{ plugins:{ legend:{ display:false } }, maintainAspectRatio:false } });
    const setTxt = (id,val)=>{ const el=document.getElementById(id); if(el) el.textContent = `${val}%`; };
    setTxt('invPaid', paid); setTxt('invUnpaid', unpaid); setTxt('invOverdue', overdue);
  }

  // Analysts Workload (horizontal bar)
  if(analystWorkloadChart){
    const names = ['A. Kumar','P. Singh','R. Shah','N. Yadav','S. Rao','V. Jain'];
    new Chart(analystWorkloadChart, {
      type: 'bar',
      data: { labels: names, datasets: [{ label: 'Samples', data: names.map(()=> rnd(5,20)), backgroundColor: '#6f42c1' }] },
      options: { indexAxis: 'y', responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ color:'#f1f1f1' } }, y:{ grid:{ display:false } } } }
    });
  }
})();
