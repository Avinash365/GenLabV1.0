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
    const invChart = new Chart(invoiceDonut, { 
      type:'doughnut', 
      data:{ 
        labels:['Paid','Unpaid','Overdue'], 
        datasets:[{ 
          data:[0,0,0], 
          backgroundColor:['#2bb673','#6c757d','#dc3545'], 
          borderWidth:0, 
          cutout:'70%' 
        }] 
      }, 
      options:{ 
        plugins:{ legend:{ display:false } }, 
        maintainAspectRatio:false 
      } 
    });

    fetch('/superadmin/dashboard/accounts-invoices-chart')
      .then(r => r.json())
      .then(d => {
         const paid = Number(d.paid||0);
         const unpaid = Number(d.unpaid||0);
         const overdue = Number(d.overdue||0);
         
         invChart.data.datasets[0].data = [paid, unpaid, overdue];
         invChart.update();

         const setVal = (id, v) => { const el=document.getElementById(id); if(el) el.textContent = v; };
         setVal('invPaid', paid);
         setVal('invUnpaid', unpaid);
         setVal('invOverdue', overdue);
      })
      .catch(e => console.error('Accounts chart error:', e));
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
