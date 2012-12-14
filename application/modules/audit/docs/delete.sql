delimiter //

create procedure deleteAudits()
begin
	delete from audit_audits_records_mistakes;
	delete from audit_audits_records;
	delete from audit_audits_records_groups;
	delete from audit_audits_forms;
	delete from audit_audits;
end//