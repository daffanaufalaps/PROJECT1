-- Site amplification factors (Fa and Fv) based on SNI 1726:2019
-- These are lookup tables for Fa and Fv values
CREATE TABLE fa_factors (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    site_class VARCHAR(10) NOT NULL,
    ss_min DECIMAL(10, 4) NOT NULL,
    ss_max DECIMAL(10, 4) NOT NULL,
    fa_value DECIMAL(10, 4) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE TABLE fv_factors (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    site_class VARCHAR(10) NOT NULL,
    s1_min DECIMAL(10, 4) NOT NULL,
    s1_max DECIMAL(10, 4) NOT NULL,
    fv_value DECIMAL(10, 4) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Insert Fa factors from SNI 1726:2019 Table 7
INSERT INTO fa_factors (site_class, ss_min, ss_max, fa_value) VALUES
-- Site Class A (hard rock) - Fa = 0.8 for all Ss values
('A', 0.00, 0.25, 0.80),
('A', 0.25, 0.50, 0.80),
('A', 0.50, 0.75, 0.80),
('A', 0.75, 1.00, 0.80),
('A', 1.00, 999.00, 0.80),
-- Site Class B (rock)
('B', 0.00, 0.25, 1.00),
('B', 0.25, 0.50, 1.00),
('B', 0.50, 0.75, 1.00),
('B', 0.75, 1.00, 1.00),
('B', 1.00, 999.00, 1.00),
-- Site Class C (very dense soil / soft rock)
('C', 0.00, 0.25, 1.20),
('C', 0.25, 0.50, 1.20),
('C', 0.50, 0.75, 1.10),
('C', 0.75, 1.00, 1.00),
('C', 1.00, 999.00, 1.00),
-- Site Class D (stiff soil)
('D', 0.00, 0.25, 1.60),
('D', 0.25, 0.50, 1.40),
('D', 0.50, 0.75, 1.20),
('D', 0.75, 1.00, 1.10),
('D', 1.00, 999.00, 1.00),
-- Site Class E (soft soil)
('E', 0.00, 0.25, 2.50),
('E', 0.25, 0.50, 1.70),
('E', 0.50, 0.75, 1.20),
('E', 0.75, 1.00, 0.90),
('E', 1.00, 999.00, 0.90);

-- Insert Fv factors from SNI 1726:2019 Table 8
INSERT INTO fv_factors (site_class, s1_min, s1_max, fv_value) VALUES
-- Site Class A
('A', 0.00, 0.10, 0.80),
('A', 0.10, 0.20, 0.80),
('A', 0.20, 0.30, 0.80),
('A', 0.30, 0.40, 0.80),
('A', 0.40, 0.50, 0.80),
('A', 0.50, 0.60, 0.80),
('A', 0.60, 999.00, 0.80),
-- Site Class B
('B', 0.00, 0.10, 1.00),
('B', 0.10, 0.20, 1.00),
('B', 0.20, 0.30, 1.00),
('B', 0.30, 0.40, 1.00),
('B', 0.40, 0.50, 1.00),
('B', 0.50, 0.60, 1.00),
('B', 0.60, 999.00, 1.00),
-- Site Class C
('C', 0.00, 0.10, 1.70),
('C', 0.10, 0.20, 1.60),
('C', 0.20, 0.30, 1.50),
('C', 0.30, 0.40, 1.40),
('C', 0.40, 0.50, 1.30),
('C', 0.50, 0.60, 1.20),
('C', 0.60, 999.00, 1.10),
-- Site Class D
('D', 0.00, 0.10, 2.40),
('D', 0.10, 0.20, 2.00),
('D', 0.20, 0.30, 1.80),
('D', 0.30, 0.40, 1.60),
('D', 0.40, 0.50, 1.50),
('D', 0.50, 0.60, 1.40),
('D', 0.60, 999.00, 1.30),
-- Site Class E
('E', 0.00, 0.10, 3.50),
('E', 0.10, 0.20, 3.20),
('E', 0.20, 0.30, 2.80),
('E', 0.30, 0.40, 2.40),
('E', 0.40, 0.50, 2.40),
('E', 0.50, 0.60, 2.40),
('E', 0.60, 999.00, 2.40);

-- Create indexes for quick lookups
CREATE INDEX idx_fa_factors_site_class ON fa_factors(site_class);
CREATE INDEX idx_fv_factors_site_class ON fv_factors(site_class);