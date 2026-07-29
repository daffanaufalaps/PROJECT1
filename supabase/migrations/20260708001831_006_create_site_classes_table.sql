-- Site class data (kelas situs) for site amplification factors
CREATE TABLE site_classes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    site_class VARCHAR(10) NOT NULL,
    description TEXT,
    geom GEOMETRY(POLYGON, 4326),
    vs30_min DECIMAL(10, 2),
    vs30_max DECIMAL(10, 2),
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create spatial index
CREATE INDEX idx_site_classes_geom ON site_classes USING GIST(geom);

-- Insert default site classes based on SNI 1726:2019
INSERT INTO site_classes (site_class, description, vs30_min, vs30_max) VALUES
('A', 'Batuan keras (Hard Rock)', 1500, NULL),
('B', 'Batuan (Rock)', 760, 1500),
('C', 'Tanah sangat padat atau batuan lunak (Very Dense Soil / Soft Rock)', 360, 760),
('D', 'Tanah kaku (Stiff Soil)', 180, 360),
('E', 'Tanah lunak (Soft Soil)', NULL, 180);