-- Quick SQL to check quotation user match
SELECT 
    q.id,
    q.quotation_number,
    q.status,
    pr.id as project_id,
    pr.user_id,
    pr.klien_id,
    u1.name as user_name,
    u2.name as klien_name
FROM quotations q
JOIN project_requests pr ON q.project_request_id = pr.id
LEFT JOIN users u1 ON pr.user_id = u1.id
LEFT JOIN users u2 ON pr.klien_id = u2.id
WHERE q.status = 'sent'
ORDER BY q.created_at DESC;
