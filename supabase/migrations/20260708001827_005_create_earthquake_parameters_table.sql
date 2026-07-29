-- Earthquake hazard parameters grid (from SNI 1726:2019)
-- This table stores spatial data for Ss and S1 values
CREATE TABLE earthquake_parameters (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    grid_id VARCHAR(50) NOT NULL,
    ss DECIMAL(10, 4) NOT NULL,
    s1 DECIMAL(10, 4) NOT NULL,
    geom GEOMETRY(POLYGON, 4326) NOT NULL,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create spatial index for earthquake parameters
CREATE INDEX idx_earthquake_parameters_geom ON earthquake_parameters USING GIST(geom);

-- Create index for grid_id lookups
CREATE INDEX idx_earthquake_parameters_grid_id ON earthquake_parameters(grid_id);