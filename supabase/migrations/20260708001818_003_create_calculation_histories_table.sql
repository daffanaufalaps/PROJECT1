-- Calculation history table
CREATE TABLE calculation_histories (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    location_geom GEOGRAPHY(POINT, 4326),
    ss DECIMAL(10, 4),
    s1 DECIMAL(10, 4),
    fa DECIMAL(10, 4),
    fv DECIMAL(10, 4),
    sms DECIMAL(10, 4),
    sm1 DECIMAL(10, 4),
    sds DECIMAL(10, 4),
    sd1 DECIMAL(10, 4),
    pga DECIMAL(10, 4),
    mmi DECIMAL(4, 2),
    risk_category VARCHAR(50),
    kds VARCHAR(10),
    narration TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create spatial index for location queries
CREATE INDEX idx_calculation_histories_geom ON calculation_histories USING GIST(location_geom);

-- Create index for date queries
CREATE INDEX idx_calculation_histories_created_at ON calculation_histories(created_at DESC);