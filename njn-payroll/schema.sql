CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  position VARCHAR(100) DEFAULT '',
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','employee') NOT NULL DEFAULT 'employee',
  daily_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
  sss DECIMAL(10,2) NOT NULL DEFAULT 0,
  philhealth DECIMAL(10,2) NOT NULL DEFAULT 0,
  pagibig DECIMAL(10,2) NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  work_date DATE NOT NULL,
  time_in DATETIME NOT NULL,
  time_out DATETIME NULL,
  INDEX (employee_id, work_date),
  FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

CREATE TABLE payslips (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employee_id INT NOT NULL,
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  days INT NOT NULL,
  reg_hours DECIMAL(8,2) NOT NULL,
  ot_hours DECIMAL(8,2) NOT NULL,
  gross DECIMAL(10,2) NOT NULL,
  sss DECIMAL(10,2) NOT NULL DEFAULT 0,
  philhealth DECIMAL(10,2) NOT NULL DEFAULT 0,
  pagibig DECIMAL(10,2) NOT NULL DEFAULT 0,
  other_ded DECIMAL(10,2) NOT NULL DEFAULT 0,
  net DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;