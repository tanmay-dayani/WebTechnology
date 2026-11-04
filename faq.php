<?php
/**
 * StudentHub Portal - FAQ Page
 * Practical 4: Collapsible Accordion & DOM Event Handling
 * Practical 6: Fetch API Dynamic Rendering & Search
 */

$page_title = "Frequently Asked Questions";
$breadcrumbs = ["Home" => "index.php", "FAQ" => ""];
require_once __DIR__ . '/includes/header.php';

// Load default FAQs from JSON file
$faqs_json = @file_get_contents(__DIR__ . '/data/faqs.json');
$faqs = json_decode($faqs_json, true) ?: [];
?>

<div class="hero" style="text-align: left; padding: 24px;">
  <h1>Frequently Asked Questions (FAQ)</h1>
  <p>Find instant answers to questions regarding account setup, workshop registrations, student authentication, and campus services.</p>
</div>

<!-- Search Input for Interactive DOM Filtering (Practical 4 & 6) -->
<div class="card" style="margin-bottom: 24px; padding: 16px;">
  <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
    <div style="flex: 1; min-width: 250px;">
      <input type="text" id="faq-search" class="form-control" placeholder="Type keywords to filter questions in real-time (e.g. password, events, dark mode)..." aria-label="Search FAQs">
    </div>
    <button id="btn-fetch-faqs" class="btn btn-outline btn-sm">Reload via Fetch API</button>
  </div>
</div>

<!-- Collapsible Accordion Container (Practical 4) -->
<div id="faq-accordion-container" style="margin-bottom: 32px;">
  <?php foreach ($faqs as $index => $item): ?>
    <div class="accordion-item faq-item" data-category="<?php echo htmlspecialchars($item['category']); ?>">
      <div class="accordion-header" tabindex="0" role="button" aria-expanded="false">
        <span>
          <span class="badge badge-info" style="margin-right: 8px;"><?php echo htmlspecialchars($item['category']); ?></span>
          <strong><?php echo htmlspecialchars($item['question']); ?></strong>
        </span>
        <span class="accordion-icon">[+]</span>
      </div>
      <div class="accordion-body">
        <p><?php echo htmlspecialchars($item['answer']); ?></p>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div id="no-faq-match" class="alert alert-info" style="display: none; text-align: center;">
  No questions match your search keyword. Try another term or <a href="contact.php">contact our helpdesk</a>.
</div>

<div class="card" style="text-align: center; padding: 24px;">
  <h3>Still Have Questions?</h3>
  <p>Cannot find what you are looking for? Reach out to our campus administration team directly.</p>
  <div style="margin-top: 12px;">
    <a href="contact.php" class="btn btn-primary">Contact Campus Support</a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('faq-search');
  const items = document.querySelectorAll('.faq-item');
  const noMatchNotice = document.getElementById('no-faq-match');

  // Real-time DOM Filtering (Practical 4)
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      const query = searchInput.value.toLowerCase().trim();
      let matchCount = 0;

      items.forEach(function (item) {
        const text = item.textContent.toLowerCase();
        if (text.includes(query)) {
          item.style.display = 'block';
          matchCount++;
        } else {
          item.style.display = 'none';
        }
      });

      noMatchNotice.style.display = (matchCount === 0) ? 'block' : 'none';
    });
  }

  // Reload dynamically via Fetch API (Practical 6)
  const fetchBtn = document.getElementById('btn-fetch-faqs');
  if (fetchBtn) {
    fetchBtn.addEventListener('click', async function () {
      fetchBtn.textContent = '⏳ Fetching...';
      try {
        const res = await fetch('data/faqs.json');
        const data = await res.json();
        const container = document.getElementById('faq-accordion-container');
        
        container.innerHTML = data.map(item => `
          <div class="accordion-item faq-item" data-category="${item.category}">
            <div class="accordion-header" tabindex="0" role="button" aria-expanded="false">
              <span>
                <span class="badge badge-info" style="margin-right: 8px;">${item.category}</span>
                <strong>${item.question}</strong>
              </span>
              <span class="accordion-icon">[+]</span>
            </div>
            <div class="accordion-body">
              <p>${item.answer}</p>
            </div>
          </div>
        `).join('');

        // Reattach accordion event listeners
        container.querySelectorAll('.accordion-header').forEach(h => {
          h.addEventListener('click', function () {
            const body = this.nextElementSibling;
            const isOpen = body.classList.contains('open');
            container.querySelectorAll('.accordion-body').forEach(b => b.classList.remove('open'));
            if (!isOpen) {
              body.classList.add('open');
              this.setAttribute('aria-expanded', 'true');
            } else {
              body.classList.remove('open');
              this.setAttribute('aria-expanded', 'false');
            }
          });
        });

        fetchBtn.textContent = 'Updated via Fetch API';
        setTimeout(() => fetchBtn.textContent = 'Reload via Fetch API', 3000);
      } catch (err) {
        alert('Fetch API error: ' + err.message);
        fetchBtn.textContent = 'Reload via Fetch API';
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

